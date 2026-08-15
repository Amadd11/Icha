<?php

namespace Database\Factories;

use App\Models\Conference;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        $conference = Conference::where('is_active', true)->first() ?? Conference::factory();
        $fee = RegistrationFee::inRandomOrder()->first() ?? RegistrationFee::factory();

        return [
            'invoice_number'      => 'INV-' . date('Y-m') . '-' . str_pad(fake()->unique()->numberBetween(100, 9999), 4, '0', STR_PAD_LEFT),
            'user_id'             => User::factory()->participant(),
            'conference_id'       => $conference->id ?? 1,
            'registration_fee_id' => $fee->id ?? 1,
            'is_early_bird'       => fake()->boolean(40),
            'currency'            => $fee->currency ?? 'IDR',
            'amount'              => $fee->price ?? 1500000,
            'status'              => 'pending',
            'notes'               => fake()->optional()->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid']);
    }

    public function waitingVerification(): static
    {
        return $this->state(fn () => ['status' => 'waiting_verification']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }
}
