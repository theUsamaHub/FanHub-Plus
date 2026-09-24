<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $avatarMedia = Media::where('media_type', 'image')->first();

        foreach ($users as $index => $user) {
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'avatar_media_id' => $avatarMedia?->id,
                    'display_name' => $user->name,
                    'bio' => "Passionate fan and community member #" . ($index + 1),
                    'theme_preference' => ($index % 2 === 0) ? 'dark' : 'light',
                    'font_size_preference' => 'medium',
                    'onboarding_completed_at' => now(),
                ]
            );
        }
    }
}
