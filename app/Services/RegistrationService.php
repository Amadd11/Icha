<?php

namespace App\Services;

use App\Helpers\CodeGenerator;
use App\Mail\InvoiceMail;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationService
{
    /**
     * Create a new conference registration synchronously and send invoice email.
     */
    public function createRegistration(User $user, array $data): Registration
    {
        $fee = RegistrationFee::findOrFail($data['registration_fee_id']);
        $confId = $fee->conference_id ?: (Conference::where('is_active', true)->value('id') ?? 1);

        // Atomic lock per user per conference prevents duplicate registrations on double-click
        $registration = Cache::lock("create_registration_{$user->id}_{$confId}", 10)->block(5, function () use ($user, $data, $fee, $confId) {
            return DB::transaction(function () use ($user, $data, $fee, $confId) {
                $amount = $fee->price;

                // Check if user already registered for this conference (idempotent / double-submit guard) with lockForUpdate
                /** @var Registration|null $existing */
                $existing = Registration::query()
                    ->where('user_id', $user->id)
                    ->where('conference_id', $confId)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if ($existing->status === 'pending' || $existing->status === 'unpaid') {
                        $existing->fill([
                            'registration_fee_id' => $fee->id,
                            'amount'              => $amount,
                            'notes'               => $data['notes'] ?? $existing->notes,
                        ])->save();
                    }
                    return $existing;
                }

                // Generate sequential collision-proof Invoice Number (INV-001, INV-002, ...)
                $invoiceNumber = CodeGenerator::next(Registration::class, 'invoice_number', 'INV');

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
        });

        // Send official invoice email to user (safeguarded so SMTP/Mail issues never crash registration)
        try {
            if (!empty($user->email)) {
                Mail::to($user->email)->send(new InvoiceMail($registration));
            }
        } catch (\Throwable $e) {
            Log::warning('Invoice email could not be sent: ' . $e->getMessage());
        }

        return $registration;
    }
}
