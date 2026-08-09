<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    /**
     * Submit payment proof synchronously.
     */
    public function submitPaymentProof(Registration $registration, string $paymentMethod, UploadedFile $proofFile): Payment
    {
        return DB::transaction(function () use ($registration, $paymentMethod, $proofFile) {
            // Save file
            $path = $proofFile->store('payments', 'public');

            // Create or Update Payment
            $payment = Payment::updateOrCreate(
                ['registration_id' => $registration->id],
                [
                    'amount'           => $registration->amount,
                    'currency'         => $registration->currency,
                    'payment_method'   => $paymentMethod,
                    'proof_file'       => $path,
                    'status'           => 'pending',
                    'rejection_reason' => null,
                    'paid_at'          => now(),
                ]
            );

            // Update registration status
            $registration->update([
                'status' => 'waiting_verification',
            ]);

            return $payment;
        });
    }

    /**
     * Verify payment proof (Admin operation) synchronously.
     */
    public function verifyPayment(Payment $payment, User $admin, string $action, ?string $rejectionReason = null): bool
    {
        return DB::transaction(function () use ($payment, $admin, $action, $rejectionReason) {
            $registration = $payment->registration;

            if ($action === 'approve') {
                $payment->update([
                    'status'           => 'verified',
                    'rejection_reason' => null,
                    'verified_at'      => now(),
                    'verified_by'      => $admin->id,
                ]);

                $registration->update([
                    'status' => 'paid',
                ]);
            } else {
                $payment->update([
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
    }
}
