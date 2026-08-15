<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_id'  => Registration::factory(),
            'amount'           => 1500000,
            'currency'         => 'IDR',
            'payment_method'   => fake()->randomElement(['Bank Transfer (Mandiri)', 'Bank Transfer (BCA)', 'Credit Card / Midtrans']),
            'proof_file'       => 'payments/dummy_proof.png',
            'status'           => 'pending',
            'rejection_reason' => null,
            'paid_at'          => now()->subDays(fake()->numberBetween(1, 14)),
            'verified_at'      => null,
            'verified_by'      => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::whereIn('role', ['super_admin', 'admin'])->first();
            return [
                'status'      => 'verified',
                'verified_at' => now(),
                'verified_by' => $admin?->id ?? 1,
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::whereIn('role', ['super_admin', 'admin'])->first();
            return [
                'status'           => 'rejected',
                'rejection_reason' => 'Bukti transfer tidak terbaca atau nominal transfer tidak sesuai.',
                'verified_at'      => now(),
                'verified_by'      => $admin?->id ?? 1,
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'      => 'pending',
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }
}
