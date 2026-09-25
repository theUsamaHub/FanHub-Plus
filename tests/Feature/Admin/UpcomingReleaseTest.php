<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Role;
use App\Models\UpcomingRelease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpcomingReleaseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);

        $this->category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
    }

    public function test_admin_can_create_upcoming_release(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.upcoming-releases.store'), [
            'title' => 'New Anime Season',
            'kind' => 'anime',
            'category_id' => $this->category->id,
            'release_date' => today()->addDays(30)->toDateString(),
            'release_label' => 'Season 2',
            'description' => 'The adventure continues.',
            'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.upcoming-releases.index'));
        $this->assertDatabaseHas('upcoming_releases', [
            'title' => 'New Anime Season',
            'kind' => 'anime',
            'release_label' => 'Season 2',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_update_and_delete_upcoming_release(): void
    {
        $release = UpcomingRelease::create([
            'title' => 'Old Title',
            'kind' => 'event',
            'release_date' => today()->addWeek(),
            'is_published' => true,
        ]);

        $this->actingAs($this->admin)->put(route('admin.upcoming-releases.update', $release), [
            'title' => 'Updated Title',
            'kind' => 'event',
            'release_date' => today()->addMonth()->toDateString(),
            'is_published' => 1,
        ])->assertRedirect(route('admin.upcoming-releases.index'));

        $this->assertSame('Updated Title', $release->fresh()->title);

        $this->actingAs($this->admin)->delete(route('admin.upcoming-releases.destroy', $release))
            ->assertRedirect(route('admin.upcoming-releases.index'));
        $this->assertDatabaseMissing('upcoming_releases', ['id' => $release->id]);
    }

    public function test_published_release_shows_on_public_upcoming_page(): void
    {
        UpcomingRelease::create([
            'title' => 'Attack of Titans Finale',
            'kind' => 'anime',
            'category_id' => $this->category->id,
            'release_date' => today()->addDays(10)->toDateString(),
            'release_label' => 'Premiere',
            'is_published' => true,
        ]);
        UpcomingRelease::create([
            'title' => 'Hidden Draft',
            'kind' => 'event',
            'is_published' => false,
        ]);

        $this->get('/discover/upcoming')->assertOk()
            ->assertSee('Attack of Titans Finale')
            ->assertDontSee('Hidden Draft');

        $this->get(route('public.upcoming-release', UpcomingRelease::first()->slug))
            ->assertOk()
            ->assertSee('Attack of Titans Finale')
            ->assertSee('Premiere');
    }

    public function test_non_admin_cannot_manage_upcoming_releases(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.upcoming-releases.index'))
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_release_appears_on_home_carousel(): void
    {
        UpcomingRelease::create([
            'title' => 'Open World RPG Launch',
            'kind' => 'game',
            'category_id' => $this->category->id,
            'release_date' => today()->addDays(5)->toDateString(),
            'release_label' => 'Launch',
            'is_published' => true,
        ]);

        $this->get('/')->assertOk()->assertSee('Open World RPG Launch');
    }
}
