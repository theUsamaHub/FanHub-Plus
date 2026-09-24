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

        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access. Can manage all resources, users, and settings.'],
            ['name' => 'Registered User', 'slug' => 'registered-user', 'description' => 'Standard registered user access. Can view and manage own profile.'],
            ['name' => 'Editor', 'slug' => 'editor', 'description' => 'Content manager. Can publish, update, and manage community content.'],
            ['name' => 'Moderator', 'slug' => 'moderator', 'description' => 'Community moderator. Can review user comments, ratings, and feedback.'],
            ['name' => 'VIP Member', 'slug' => 'vip-member', 'description' => 'Special tier user with access to premium content and events.'],
            ['name' => 'Contributor', 'slug' => 'contributor', 'description' => 'Verified content creator submitting articles and media.'],
            ['name' => 'Creator', 'slug' => 'creator', 'description' => 'Artist and content maker profile.'],
            ['name' => 'Reviewer', 'slug' => 'reviewer', 'description' => 'Designated content reviewer and critic.'],
            ['name' => 'Subscriber', 'slug' => 'subscriber', 'description' => 'Paid subscriber with early access privileges.'],
            ['name' => 'Guest', 'slug' => 'guest', 'description' => 'Basic guest role with read-only capabilities.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
