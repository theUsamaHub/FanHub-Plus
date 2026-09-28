<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::where('slug', 'user')->update([
            'slug' => 'registered-user',
            'name' => 'Registered User',
        ]);

        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access. Can manage all resources, users, and settings.'],
            ['name' => 'Registered User', 'slug' => 'registered-user', 'description' => 'Standard registered user access. Can view and manage own profile.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
