<?php

namespace App\Services;

use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    /**
     * Create a new conference registration synchronously.
     */
    public function createRegistration(User $user, array $data): Registration
    {
        return DB::transaction(function () use ($user, $data) {
            $regType = RegistrationType::findOrFail($data['registration_type_id']);
            $conference = Conference::findOrFail($regType->conference_id);

            // Determine if Early Bird applies
            $isEarlyBird = false;
            if ($regType->early_bird_deadline && now()->lte($regType->early_bird_deadline)) {
                $isEarlyBird = true;
            }

            // Calculate Amount based on Currency & Early Bird
            $currency = $data['currency'] ?? 'IDR';
            if ($currency === 'USD') {
                $amount = $isEarlyBird ? $regType->early_bird_price_usd : $regType->regular_price_usd;
            } else {
                $amount = $isEarlyBird ? $regType->early_bird_price_idr : $regType->regular_price_idr;
            }

            // Generate Short Invoice Number (INV-001, INV-002, ...)
            $nextInvNum = Registration::where('conference_id', $conference->id)->count() + 1;
            $invoiceNumber = 'INV-' . str_pad($nextInvNum, 3, '0', STR_PAD_LEFT);

            // Create Registration Record
            return Registration::create([
                'invoice_number'       => $invoiceNumber,
                'user_id'              => $user->id,
                'conference_id'        => $conference->id,
                'registration_type_id' => $regType->id,
                'is_early_bird'        => $isEarlyBird,
                'currency'             => $currency,
                'amount'               => $amount,
                'status'               => 'pending',
                'notes'                => $data['notes'] ?? null,
            ]);
        });
    }
}
