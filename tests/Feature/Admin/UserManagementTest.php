<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_list_users_with_filters(): void
    {
        $member = User::factory()->create(['name' => 'Findable Member']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'Findable']))
            ->assertOk()
            ->assertSee('Findable Member');

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'admin']))
            ->assertOk()
            ->assertSee($this->admin->email);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['verified' => '1']))
            ->assertOk();
    }

    public function test_admin_can_view_user_detail_with_related_activity(): void
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee($member->email)
            ->assertSee('Activity summary')
            ->assertSee('Recent submissions');
    }

    public function test_admin_can_update_user_and_roles(): void
    {
        $member = User::factory()->create();
        Role::firstOrCreate(['slug' => 'registered-user'], ['name' => 'Registered User']);

        $this->actingAs($this->admin)->put(route('admin.users.update', $member), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => ['registered-user'],
        ])->assertRedirect(route('admin.users.index'));

        $member->refresh();
        $this->assertSame('Updated Name', $member->name);
        $this->assertSame('updated@example.com', $member->email);
        $this->assertTrue($member->hasRole('registered-user'));
        $this->assertFalse($member->hasRole('admin'));
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $member))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('admin.users.index'))->assertRedirect(route('user.dashboard'));
    }
}
