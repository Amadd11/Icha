<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationService
{
    /**
     * Create a new conference registration synchronously and send invoice email.
     */
    public function createRegistration(User $user, array $data): Registration
    {
        $registration = DB::transaction(function () use ($user, $data) {
            $fee = RegistrationFee::findOrFail($data['registration_fee_id']);
            $conference = Conference::active()->first() ?? Conference::first();
            $confId = $conference?->id ?? 1;

            $amount = $fee->price;

            // Check if user already registered for this conference (idempotent / double-submit guard)
            $existing = Registration::where('user_id', $user->id)
                ->where('conference_id', $confId)
                ->first();

            if ($existing) {
                if ($existing->status === 'pending' || $existing->status === 'unpaid') {
                    $existing->update([
                        'registration_fee_id' => $fee->id,
                        'amount'              => $amount,
                        'notes'               => $data['notes'] ?? $existing->notes,
                    ]);
                }
                return $existing;
            }

            // Generate Short Collision-Proof Invoice Number (INV-001, INV-002, ...)
            $maxId = (Registration::withTrashed()->max('id') ?? 0) + 1;
            $invoiceNumber = 'INV-' . str_pad($maxId, 3, '0', STR_PAD_LEFT);

            // Create Registration Record
            return Registration::create([
                'invoice_number'      => $invoiceNumber,
                'user_id'             => $user->id,
                'conference_id'       => $confId,
                'registration_fee_id' => $fee->id,
                'is_early_bird'       => false,
                'currency'            => 'IDR',
                'amount'              => $amount,
                'status'              => 'pending',
                'notes'               => $data['notes'] ?? null,
            ]);
        });

        // Send official invoice email to user
        try {
            if ($user->email) {
                Mail::to($user->email)->send(new InvoiceMail($registration));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $registration;
    }
}
