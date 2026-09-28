<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $admin->roles()->sync([Role::where('slug', 'admin')->firstOrFail()->id]);

        $testUser = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $memberRole = Role::where('slug', 'registered-user')->firstOrFail();
        $testUser->roles()->sync([$memberRole->id]);

        $additionalUsers = [
            ['name' => 'Alex Rivera', 'email' => 'alex.rivera@example.com'],
            ['name' => 'Samantha Chen', 'email' => 'samantha.chen@example.com'],
            ['name' => 'Marcus Vance', 'email' => 'marcus.vance@example.com'],
            ['name' => 'Elena Rostova', 'email' => 'elena.rostova@example.com'],
            ['name' => 'Daisuke Sato', 'email' => 'daisuke.sato@example.com'],
            ['name' => 'Chloe Bennett', 'email' => 'chloe.bennett@example.com'],
            ['name' => 'Liam O\'Connor', 'email' => 'liam.oconnor@example.com'],
            ['name' => 'Zahra Ahmed', 'email' => 'zahra.ahmed@example.com'],
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
            $user->roles()->sync([$memberRole->id]);
        }
    }
}
