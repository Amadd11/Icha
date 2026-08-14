<?php

namespace Database\Seeders;

use App\Models\Conference;
use App\Models\RegistrationFee;
use Illuminate\Database\Seeder;

class RegistrationFeeSeeder extends Seeder
{
    public function run(): void
    {
        $conference = Conference::active()->first() ?? Conference::first();

        if (! $conference) {
            return;
        }

        $fees = [
            // 1. Registrasi Offline (Onsite)
            [
                'name'      => 'Presenter Nasional Offline',
                'mode'      => 'offline',
                'type'      => 'presenter',
                'category'  => 'national',
                'price'     => 1500000,
                'is_active' => true,
            ],
            [
                'name'      => 'Presenter Mahasiswa Offline',
                'mode'      => 'offline',
                'type'      => 'presenter',
                'category'  => 'student',
                'price'     => 500000,
                'is_active' => true,
            ],
            [
                'name'      => 'Peserta Non-Presenter Offline',
                'mode'      => 'offline',
                'type'      => 'non_presenter',
                'category'  => 'national',
                'price'     => 750000,
                'is_active' => true,
            ],
            [
                'name'      => 'Presenter Internasional Offline',
                'mode'      => 'offline',
                'type'      => 'presenter',
                'category'  => 'international',
                'price'     => 2400000,
                'is_active' => true,
            ],

            // 2. Registrasi Online (Virtual)
            [
                'name'      => 'Presenter Nasional Online',
                'mode'      => 'online',
                'type'      => 'presenter',
                'category'  => 'national',
                'price'     => 1000000,
                'is_active' => true,
            ],
            [
                'name'      => 'Presenter Mahasiswa Online',
                'mode'      => 'online',
                'type'      => 'presenter',
                'category'  => 'student',
                'price'     => 250000,
                'is_active' => true,
            ],
            [
                'name'      => 'Peserta Non-Presenter Online (Webinar Only)',
                'mode'      => 'online',
                'type'      => 'non_presenter',
                'category'  => null,
                'price'     => 250000,
                'is_active' => true,
            ],
            [
                'name'      => 'Presenter Internasional Online',
                'mode'      => 'online',
                'type'      => 'presenter',
                'category'  => 'international',
                'price'     => 1600000,
                'is_active' => true,
            ],
        ];

        foreach ($fees as $fee) {
            RegistrationFee::updateOrCreate(
                [
                    'conference_id' => $conference->id,
                    'name'          => $fee['name'],
                ],
                [
                    'mode'      => $fee['mode'],
                    'type'      => $fee['type'],
                    'category'  => $fee['category'],
                    'price'     => $fee['price'],
                    'is_active' => $fee['is_active'],
                ]
            );
        }
    }
}
