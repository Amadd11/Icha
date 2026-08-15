<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Conference;
use App\Models\Sponsor;
use App\Models\Timeline;
use Illuminate\Database\Seeder;

class ConferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Main Conference
        $conference = Conference::updateOrCreate(
            ['title' => 'ICHA 2026'],
            [
                'slug'        => 'icha-2026',
                'year'        => 2026,
                'tagline'     => '10th International Conference on Healthcare Administration',
                'description' => 'ICHA 2026 brings together researchers, academics, practitioners, students, and policymakers to share ideas, innovations, and best practices for the future of health systems.',
                'start_date'  => '2026-11-10',
                'end_date'    => '2026-11-11',
                'venue'       => 'Surabaya International Convention Center',
                'city'        => 'Surabaya',
                'country'     => 'Indonesia',
                'theme'       => 'Healthcare Administration for a Sustainable Future',
                'email'       => 'info@icha2026.id',
                'status'      => 'active',
                'is_active'   => true,
            ]
        );

        // Scientific Tracks (Categories)
        $tracks = [
            [
                'badge'       => 'Track 01',
                'name'        => 'Healthcare Education & Human Capital Development',
                'description' => 'Building competent professionals, future leaders, and resilient healthcare workforce.',
            ],
            [
                'badge'       => 'Track 02',
                'name'        => 'Hospital Leadership & Management',
                'description' => 'Leadership, governance, strategy, and operational excellence in healthcare organizations.',
            ],
            [
                'badge'       => 'Track 03',
                'name'        => 'Quality, Innovation & Digital Health',
                'description' => 'Quality improvement, patient safety, technology, and digital transformation in health services.',
            ],
            [
                'badge'       => 'Track 04',
                'name'        => 'Health Policy, Research & Sustainability',
                'description' => 'Health policy, health economics, research methods, and sustainable health systems development.',
            ],
        ];

        foreach ($tracks as $track) {
            Category::updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $track['name']],
                array_merge($track, ['conference_id' => $conference->id])
            );
        }

        // Timelines
        $timelines = [
            [
                'title'       => 'Preparation & Launch',
                'period'      => 'July - August 2026',
                'description' => "24-25 Jul: PIPMARSI Meeting\n5 Aug: TOR & Branding\n8 Aug: Committee\n11 Aug: Call for Abstract",
                'order'       => 1,
            ],
            [
                'title'       => 'Speaker Confirmation & Registration Opens',
                'period'      => 'September 2026',
                'description' => "7 Sep: Keynote invitations\n21 Sep: Keynote confirmed\n22 Sep: Registration Opens",
                'order'       => 2,
            ],
            [
                'title'       => 'Abstract Selection & Review',
                'period'      => 'October 2026',
                'description' => "3 Oct: Abstract deadline\n4-10 Oct: Abstract review\n12 Oct: Acceptance notification",
                'order'       => 3,
            ],
        ];

        foreach ($timelines as $t) {
            Timeline::updateOrCreate(
                ['conference_id' => $conference->id, 'title' => $t['title']],
                array_merge($t, ['conference_id' => $conference->id])
            );
        }

        // Sponsors
        $sponsors = [
            ['name' => 'Kemenkes RI',       'tier' => 'title',    'order' => 1],
            ['name' => 'PERSI',             'tier' => 'platinum', 'order' => 2],
            ['name' => 'PIPMARSI',          'tier' => 'gold',     'order' => 3],
            ['name' => 'Kalbe Farma',       'tier' => 'silver',   'order' => 4],
            ['name' => 'Siloam Hospitals', 'tier' => 'bronze',   'order' => 5],
        ];

        foreach ($sponsors as $s) {
            Sponsor::updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $s['name']],
                array_merge($s, ['conference_id' => $conference->id, 'is_active' => true])
            );
        }
    }
}
