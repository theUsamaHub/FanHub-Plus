<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $testUser = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $testUser->assignRole('registered-user');

        $additionalUsers = [
            ['name' => 'Alex Rivera', 'email' => 'alex.rivera@example.com', 'role' => 'editor'],
            ['name' => 'Samantha Chen', 'email' => 'samantha.chen@example.com', 'role' => 'moderator'],
            ['name' => 'Marcus Vance', 'email' => 'marcus.vance@example.com', 'role' => 'vip-member'],
            ['name' => 'Elena Rostova', 'email' => 'elena.rostova@example.com', 'role' => 'contributor'],
            ['name' => 'Daisuke Sato', 'email' => 'daisuke.sato@example.com', 'role' => 'creator'],
            ['name' => 'Chloe Bennett', 'email' => 'chloe.bennett@example.com', 'role' => 'reviewer'],
            ['name' => 'Liam O\'Connor', 'email' => 'liam.oconnor@example.com', 'role' => 'subscriber'],
            ['name' => 'Zahra Ahmed', 'email' => 'zahra.ahmed@example.com', 'role' => 'registered-user'],
        ];

        foreach ($additionalUsers as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole($u['role']);
        }
    }
}
