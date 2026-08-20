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

            $amount = $fee->price;

            // Generate Short Invoice Number (INV-001, INV-002, ...)
            $nextInvNum = Registration::count() + 1;
            $invoiceNumber = 'INV-' . str_pad($nextInvNum, 3, '0', STR_PAD_LEFT);

            // Create Registration Record
            return Registration::create([
                'invoice_number'      => $invoiceNumber,
                'user_id'             => $user->id,
                'conference_id'       => $conference?->id ?? 1,
                'registration_fee_id' => $fee->id,
                'is_early_bird'       => false,
                'currency'            => $data['currency'] ?? 'IDR',
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
