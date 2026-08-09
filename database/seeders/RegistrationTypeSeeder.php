<?php

namespace Database\Seeders;

use App\Models\Conference;
use App\Models\RegistrationType;
use Illuminate\Database\Seeder;

class RegistrationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $conference = Conference::active()->first() ?? Conference::first();

        if (! $conference) {
            return;
        }

        $regTypes = [
            // Student Types
            [
                'name'                 => 'Student - International Presenter',
                'category'             => 'student',
                'role_type'            => 'presenter',
                'is_international'     => true,
                'early_bird_price_idr' => 250000,
                'regular_price_idr'    => 350000,
                'early_bird_price_usd' => 17,
                'regular_price_usd'    => 24,
            ],
            [
                'name'                 => 'Student - Local Presenter',
                'category'             => 'student',
                'role_type'            => 'presenter',
                'is_international'     => false,
                'early_bird_price_idr' => 200000,
                'regular_price_idr'    => 300000,
                'early_bird_price_usd' => 0,
                'regular_price_usd'    => 0,
            ],
            [
                'name'                 => 'Student - General Attendee',
                'category'             => 'student',
                'role_type'            => 'attendee',
                'is_international'     => false,
                'early_bird_price_idr' => 150000,
                'regular_price_idr'    => 200000,
                'early_bird_price_usd' => 12,
                'regular_price_usd'    => 15,
            ],
            // Non-Student Types
            [
                'name'                 => 'Non-Student - International Presenter',
                'category'             => 'non_student',
                'role_type'            => 'presenter',
                'is_international'     => true,
                'early_bird_price_idr' => 500000,
                'regular_price_idr'    => 600000,
                'early_bird_price_usd' => 33,
                'regular_price_usd'    => 38,
            ],
            [
                'name'                 => 'Non-Student - Local Presenter',
                'category'             => 'non_student',
                'role_type'            => 'presenter',
                'is_international'     => false,
                'early_bird_price_idr' => 300000,
                'regular_price_idr'    => 400000,
                'early_bird_price_usd' => 0,
                'regular_price_usd'    => 0,
            ],
            [
                'name'                 => 'Non-Student - General Attendee',
                'category'             => 'non_student',
                'role_type'            => 'attendee',
                'is_international'     => false,
                'early_bird_price_idr' => 200000,
                'regular_price_idr'    => 300000,
                'early_bird_price_usd' => 15,
                'regular_price_usd'    => 21,
            ],
        ];

        foreach ($regTypes as $rt) {
            RegistrationType::updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $rt['name']],
                array_merge($rt, [
                    'conference_id'       => $conference->id,
                    'early_bird_deadline' => '2026-09-30',
                    'is_active'           => true,
                ])
            );
        }
    }
}
