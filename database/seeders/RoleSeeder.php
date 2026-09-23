<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Migrate legacy 'user' role to the final schema naming
        Role::where('slug', 'user')->update([
            'slug' => 'registered-user',
            'name' => 'Registered User',
        ]);

        Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Full system access. Can manage all resources, users, and settings.',
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'registered-user'],
            [
                'name' => 'Registered User',
                'description' => 'Standard registered user access. Can view and manage own profile.',
            ]
        );
    }
}
