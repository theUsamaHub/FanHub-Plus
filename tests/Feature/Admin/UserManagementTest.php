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

    public function test_admin_can_create_another_admin_only(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Add Admin User')
            ->assertSee('Admin');

        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Second Admin',
            'email' => 'admin2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $created = User::firstWhere('email', 'admin2@example.com');
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('admin'));
        $this->assertFalse($created->hasRole('registered-user'));
    }

    public function test_user_create_requires_name_email_password(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_user_edit_is_not_available(): void
    {
        $member = User::factory()->create();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.users.edit'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.users.update'));

        $this->actingAs($this->admin)->get('/admin/users/'.$member->id.'/edit')->assertNotFound();
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
