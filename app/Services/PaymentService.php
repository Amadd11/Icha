<?php

namespace App\Services;

use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentRejectedMail;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\SubmissionException;

class PaymentService
{
    /**
     * Submit payment proof synchronously.
     */
    public function submitPaymentProof(Registration $registration, string $paymentMethod, UploadedFile $proofFile): Payment
    {
        // Early check before uploading file to prevent orphan files on disk
        if ($registration->status === 'paid') {
            throw new SubmissionException('Pembayaran untuk pendaftaran ini sudah lunas dan terverifikasi.', 422);
        }

        $preCheck = Payment::where('registration_id', $registration->id)->first();
        if ($preCheck && $preCheck->status === 'verified') {
            throw new SubmissionException('Pembayaran yang sudah diverifikasi tidak dapat diubah.', 422);
        }

        $path = null;
        try {
            return Cache::lock("submit_payment_proof_{$registration->id}", 10)->block(5, function () use ($registration, $paymentMethod, $proofFile, &$path) {
                // Save file to private local disk
                $path = $proofFile->store('payments', 'local');

                return DB::transaction(function () use ($registration, $paymentMethod, $path) {
                    // Check existing payment with lockForUpdate to guarantee no duplicate rows
                    $existingPayment = Payment::where('registration_id', $registration->id)->lockForUpdate()->first();

                    if ($existingPayment) {
                        if ($existingPayment->status === 'verified') {
                            throw new SubmissionException('Pembayaran yang sudah diverifikasi tidak dapat diubah.', 422);
                        }

                        // Delete previous file from storage if replaced
                        if ($existingPayment->proof_file && $existingPayment->proof_file !== $path) {
                            if (Storage::disk('local')->exists($existingPayment->proof_file)) {
                                Storage::disk('local')->delete($existingPayment->proof_file);
                            } elseif (Storage::disk('public')->exists($existingPayment->proof_file)) {
                                Storage::disk('public')->delete($existingPayment->proof_file);
                            }
                        }

                        $existingPayment->update([
                            'amount'           => $registration->amount,
                            'currency'         => $registration->currency,
                            'payment_method'   => $paymentMethod,
                            'proof_file'       => $path,
                            'status'           => 'pending',
                            'rejection_reason' => null,
                            'paid_at'          => now(),
                        ]);
                        $payment = $existingPayment;
                    } else {
                        $payment = Payment::create([
                            'registration_id'  => $registration->id,
                            'amount'           => $registration->amount,
                            'currency'         => $registration->currency,
                            'payment_method'   => $paymentMethod,
                            'proof_file'       => $path,
                            'status'           => 'pending',
                            'rejection_reason' => null,
                            'paid_at'          => now(),
                        ]);
                    }

                    // Update registration status using explicit state transition
                    $registration->transitionTo('waiting_verification');

                    return $payment;
                });
            });
        } catch (\Throwable $e) {
            // Delete uploaded file if anything failed to prevent orphan storage files
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    /**
     * Verify payment proof (Admin operation) synchronously.
     */
    public function verifyPayment(Payment $payment, User $admin, string $action, ?string $rejectionReason = null): bool
    {
        // Atomic lock prevents admin double-click from firing duplicate verification emails
        return Cache::lock("verify_payment_{$payment->id}", 10)->block(5, function () use ($payment, $admin, $action, $rejectionReason) {
            $alreadyProcessed = false;

            $result = DB::transaction(function () use ($payment, $admin, $action, $rejectionReason, &$alreadyProcessed) {
                // Lock payment row to check current status
                $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->first();

                // Verified payments are frozen and cannot be rejected or modified
                if ($lockedPayment->status === 'verified' && $action !== 'approve') {
                    throw new \DomainException('Pembayaran yang sudah diverifikasi dibekukan dan tidak dapat diubah atau ditolak.');
                }

                // Idempotent guard: if already in the target state, do not re-process or re-send email
                if ($action === 'approve' && $lockedPayment->status === 'verified') {
                    $alreadyProcessed = true;
                    return true;
                }
                if ($action === 'reject' && $lockedPayment->status === 'rejected') {
                    $alreadyProcessed = true;
                    return true;
                }

                $registration = $lockedPayment->registration;

                $lockedPayment->transitionTo($action === 'approve' ? 'verified' : 'rejected');
                $lockedPayment->update([
                    'rejection_reason' => $action === 'approve' ? null : $rejectionReason,
                    'verified_at'      => now(),
                    'verified_by'      => $admin->id,
                ]);

                $registration->transitionTo($action === 'approve' ? 'paid' : 'rejected');

                return true;
            });

            // Send email only once (skip if double-click detected)
            if (!$alreadyProcessed) {
                try {
                    $payment->loadMissing(['registration.user', 'registration.conference', 'registration.registrationFee']);
                    $userEmail = $payment->registration->user->email ?? null;

                    if ($userEmail) {
                        if ($action === 'approve') {
                            Mail::to($userEmail)->send(new PaymentApprovedMail($payment));
                        } else {
                            Mail::to($userEmail)->send(new PaymentRejectedMail($payment));
                        }
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return $result;
        });
    }
}
