<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);
    }

    public function test_admin_can_update_profile_preferences(): void
    {
        $avatar = Media::create([
            'uploaded_by' => $this->admin->id,
            'path' => 'uploads/images/avatar.png',
            'original_filename' => 'avatar.png',
            'mime_type' => 'image/png',
            'media_type' => 'image',
            'size_bytes' => 100,
            'disk' => 'public',
        ]);

        $this->actingAs($this->admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Display name')
            ->assertSee('Theme')
            ->assertSee('Font size');

        $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Admin User',
            'email' => $this->admin->email,
            'display_name' => 'Admin Display',
            'bio' => 'Fandom platform admin.',
            'avatar_media_id' => $avatar->id,
            'theme_preference' => 'dark',
            'font_size_preference' => 'large',
        ])->assertRedirect(route('profile.edit'));

        $profile = UserProfile::firstWhere('user_id', $this->admin->id);
        $this->assertSame('Admin Display', $profile->display_name);
        $this->assertSame('Fandom platform admin.', $profile->bio);
        $this->assertSame($avatar->id, $profile->avatar_media_id);
        $this->assertSame('dark', $profile->theme_preference);
        $this->assertSame('large', $profile->font_size_preference);
    }

    public function test_profile_preferences_reject_invalid_values(): void
    {
        $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Admin User',
            'email' => $this->admin->email,
            'theme_preference' => 'neon',
            'font_size_preference' => 'huge',
        ])->assertSessionHasErrors(['theme_preference', 'font_size_preference']);
    }

    public function test_profile_preferences_can_be_updated_without_avatar(): void
    {
        $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Admin User',
            'email' => $this->admin->email,
            'display_name' => 'Just Display',
            'theme_preference' => 'system',
            'font_size_preference' => 'medium',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $this->admin->id,
            'display_name' => 'Just Display',
            'theme_preference' => 'system',
            'font_size_preference' => 'medium',
        ]);
    }
}
