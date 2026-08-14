<?php

namespace Database\Seeders;

use App\Models\Category;
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

        $categoryIds = Category::pluck('id')->toArray();

        // Reviewer 1 (Primary)
        $r1 = User::updateOrCreate(
            ['email' => 'reviewer@gmail.com'],
            [
                'name'     => 'Reviewer',
                'password' => bcrypt('password'),
                'role'     => 'reviewer',
            ]
        );
        if (!empty($categoryIds)) {
            $r1->categories()->sync($categoryIds);
        }

        // Reviewer 2
        $r2 = User::updateOrCreate(
            ['email' => 'reviewer@icha.com'],
            [
                'name'     => 'Prof. Reviewer ICHA',
                'password' => bcrypt('password'),
                'role'     => 'reviewer',
            ]
        );
        if (!empty($categoryIds)) {
            $r2->categories()->sync($categoryIds);
        }

        // Reviewer 3
        $r3 = User::updateOrCreate(
            ['email' => 'reviewer2@icha.com'],
            [
                'name'     => 'Dr. Reviewer 2',
                'password' => bcrypt('password'),
                'role'     => 'reviewer',
            ]
        );
        if (!empty($categoryIds)) {
            $r3->categories()->sync($categoryIds);
        }

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
