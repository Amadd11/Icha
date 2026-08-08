<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Committee;
use App\Models\Conference;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Users
        User::updateOrCreate(
            ['email' => 'superadmin@icha.com'],
            [
                'name' => 'Super Admin ICHA',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@icha.com'],
            [
                'name' => 'Admin ICHA',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'participant@icha.com'],
            [
                'name' => 'Test Participant',
                'password' => bcrypt('password'),
                'role' => 'participant',
            ]
        );

        // Conference
        $conference = Conference::updateOrCreate(
            ['title' => 'ICHA 2026'],
            [
                'tagline'     => '11th International Conference on Healthcare Administration',
                'description' => 'ICHA 2026 brings together researchers, academics, practitioners, students, and policymakers to share ideas, innovations, and best practices for the future of health systems.',
                'start_date'  => '2026-11-10',
                'end_date'    => '2026-11-11',
                'venue'       => 'Surabaya International Convention Center',
                'city'        => 'Surabaya',
                'country'     => 'Indonesia',
                'theme'       => 'Healthcare Administration for a Sustainable Future',
                'email'       => 'info@icha2026.id',
                'website'     => 'https://icha2026.id',
                'status'      => 'active',
                'is_active'   => true,
            ]
        );

        // Scientific Tracks (Categories)
        $tracks = [
            [
                'badge'       => 'Track 01',
                'icon'        => '🎓',
                'name'        => 'Healthcare Administration Education',
                'description' => 'Curriculum, learning innovation, and academic development in healthcare administration.',
                'order'       => 1,
            ],
            [
                'badge'       => 'Track 02',
                'icon'        => '🏥',
                'name'        => 'Hospital Leadership & Management',
                'description' => 'Leadership, governance, strategy, and operational excellence in healthcare organizations.',
                'order'       => 2,
            ],
            [
                'badge'       => 'Track 03',
                'icon'        => '🤖',
                'name'        => 'Quality, Innovation & Digital Health',
                'description' => 'Quality improvement, patient safety, technology, and digital transformation in health services.',
                'order'       => 3,
            ],
            [
                'badge'       => 'Track 04',
                'icon'        => '🌍',
                'name'        => 'Health Policy, Research & Sustainability',
                'description' => 'Health policy, health economics, research methods, and sustainable health systems development.',
                'order'       => 4,
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
            ['name' => 'Kemenkes RI',   'tier' => 'title',     'order' => 1],
            ['name' => 'PERSI',         'tier' => 'platinum',  'order' => 2],
            ['name' => 'PIPMARSI',      'tier' => 'gold',      'order' => 3],
            ['name' => 'Kalbe Farma',   'tier' => 'silver',    'order' => 4],
            ['name' => 'Silooam Hospitals', 'tier' => 'bronze','order' => 5],
        ];

        foreach ($sponsors as $s) {
            Sponsor::updateOrCreate(
                ['conference_id' => $conference->id, 'name' => $s['name']],
                array_merge($s, ['conference_id' => $conference->id, 'is_active' => true])
            );
        }
    }
}
