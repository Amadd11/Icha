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

class PaymentService
{
    /**
     * Submit payment proof by Registration ID.
     */
    public function submitProof(int $registrationId, string $paymentMethod, UploadedFile $proofFile, ?User $user = null): Payment
    {
        $registration = Registration::findOrFail($registrationId);
        return $this->submitPaymentProof($registration, $paymentMethod, $proofFile);
    }

    /**
     * Submit payment proof synchronously.
     */
    public function submitPaymentProof(Registration $registration, string $paymentMethod, UploadedFile $proofFile): Payment
    {
        $path = null;
        try {
            return Cache::lock("submit_payment_proof_{$registration->id}", 10)->block(5, function () use ($registration, $paymentMethod, $proofFile, &$path) {
                // Save file
                $path = $proofFile->store('payments', 'public');

                return DB::transaction(function () use ($registration, $paymentMethod, $path) {
                    // Check existing payment with lockForUpdate to guarantee no duplicate rows
                    $existingPayment = Payment::where('registration_id', $registration->id)->lockForUpdate()->first();

                    if ($existingPayment) {
                        // Delete previous file from storage if replaced
                        if ($existingPayment->proof_file && $existingPayment->proof_file !== $path) {
                            Storage::disk('public')->delete($existingPayment->proof_file);
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

                    // Update registration status
                    $registration->update([
                        'status' => 'waiting_verification',
                    ]);

                    return $payment;
                });
            });
        } catch (\Throwable $e) {
            // Delete uploaded file if anything failed to prevent orphan storage files
            if ($path) {
                Storage::disk('public')->delete($path);
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

                if ($action === 'approve') {
                    $lockedPayment->update([
                        'status'           => 'verified',
                        'rejection_reason' => null,
                        'verified_at'      => now(),
                        'verified_by'      => $admin->id,
                    ]);

                    $registration->update([
                        'status' => 'paid',
                    ]);
                } else {
                    $lockedPayment->update([
                        'status'           => 'rejected',
                        'rejection_reason' => $rejectionReason,
                        'verified_at'      => now(),
                        'verified_by'      => $admin->id,
                    ]);

                    $registration->update([
                        'status' => 'rejected',
                    ]);
                }

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
