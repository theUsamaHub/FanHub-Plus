<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserProfileSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_profiles_can_be_seeded_again_after_preference_columns_are_removed(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Schema::hasColumn('user_profiles', 'theme_preference'));
        $this->assertFalse(Schema::hasColumn('user_profiles', 'font_size_preference'));

        $this->seed(UserProfileSeeder::class);
        $this->seed(UserProfileSeeder::class);

        $this->assertDatabaseCount('user_profiles', 1);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'display_name' => $user->name,
            'avatar_media_id' => null,
        ]);
        $this->assertNotNull($user->fresh()->profile->onboarding_completed_at);
    }
}
