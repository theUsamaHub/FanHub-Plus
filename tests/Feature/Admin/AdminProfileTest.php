<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
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

    public function test_admin_profile_is_basic_only(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Name')
            ->assertSee('Email');

        $response->assertDontSee('Display name');
        $response->assertDontSee('Font size');
        $response->assertDontSee('theme_preference');
    }

    public function test_admin_can_update_name_and_email(): void
    {
        $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Admin User',
            'email' => 'admin-updated@example.com',
        ])->assertRedirect(route('profile.edit'));

        $this->admin->refresh();
        $this->assertSame('Admin User', $this->admin->name);
        $this->assertSame('admin-updated@example.com', $this->admin->email);
    }

    public function test_admin_update_ignores_registered_user_profile_fields(): void
    {
        $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Admin User',
            'email' => $this->admin->email,
            'display_name' => 'Should Be Ignored',
            'theme_preference' => 'neon',
            'font_size_preference' => 'huge',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseCount('user_profiles', 0);
    }

    public function test_registered_user_sees_profile_extras(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Display name')
            ->assertSee('Font size');
    }

    public function test_registered_user_can_update_profile_preferences(): void
    {
        $member = User::factory()->create();
        $avatar = Media::create([
            'uploaded_by' => $member->id,
            'path' => 'uploads/images/a.png',
            'original_filename' => 'a.png',
            'mime_type' => 'image/png',
            'media_type' => 'image',
            'size_bytes' => 10,
            'disk' => 'public',
        ]);

        $this->actingAs($member)->patch(route('profile.update'), [
            'name' => $member->name,
            'email' => $member->email,
            'display_name' => 'Member Display',
            'bio' => 'Hello',
            'avatar_media_id' => $avatar->id,
            'theme_preference' => 'dark',
            'font_size_preference' => 'large',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $member->id,
            'display_name' => 'Member Display',
            'theme_preference' => 'dark',
            'font_size_preference' => 'large',
        ]);
    }
}
