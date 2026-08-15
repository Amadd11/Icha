<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => 'participant',
            'remember_token'    => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (!$user->profile) {
                Profile::create([
                    'user_id'              => $user->id,
                    'institution'          => fake()->randomElement([
                        'Universitas Muhammadiyah Surabaya',
                        'Universitas Airlangga',
                        'Universitas Brawijaya',
                        'Universitas Indonesia',
                        'RSUD Dr. Soetomo',
                        'RSUP Dr. Sardjito',
                        'RS Siloam Hospitals',
                        'Kementerian Kesehatan RI',
                    ]),
                    'phone'                => fake()->phoneNumber(),
                    'country'              => fake()->country(),
                    'city'                 => fake()->city(),
                    'participant_category' => fake()->randomElement(['student', 'non_student']),
                    'gender'               => fake()->randomElement(['male', 'female']),
                ]);
            }
        });
    }

    public function participant(): static
    {
        return $this->state(fn () => ['role' => 'participant']);
    }

    public function reviewer(): static
    {
        return $this->state(fn () => ['role' => 'reviewer']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => 'super_admin']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
