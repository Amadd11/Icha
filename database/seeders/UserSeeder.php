<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@icha.com'],
            [
                'name'     => 'Super Admin ICHA',
                'password' => bcrypt('password'),
                'role'     => 'super_admin',
            ]
        );

        // Admin
        User::updateOrCreate(
            ['email' => 'admin@icha.com'],
            [
                'name'     => 'Admin ICHA',
                'password' => bcrypt('password'),
                'role'     => 'admin',
            ]
        );

        // Participant
        User::updateOrCreate(
            ['email' => 'participant@icha.com'],
            [
                'name'     => 'Test Participant',
                'password' => bcrypt('password'),
                'role'     => 'participant',
            ]
        );
    }
}
