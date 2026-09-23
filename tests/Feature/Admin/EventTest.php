<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
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

    public function test_admin_can_create_event(): void
    {
        $this->actingAs($this->admin)->post(route('admin.events.store'), [
            'title' => 'Anime Expo',
            'description' => 'Annual convention',
            'category_id' => $this->category->id,
            'city' => 'Los Angeles',
            'venue' => 'Convention Center',
            'address' => '123 Main St',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'start_at' => '2026-12-01 10:00:00',
            'end_at' => '2026-12-03 18:00:00',
            'ticket_url' => 'https://example.com/tickets',
            'status' => 'published',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Anime Expo',
            'city' => 'Los Angeles',
            'status' => 'published',
        ]);
    }

    public function test_event_requires_title_city_and_start(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), [])
            ->assertSessionHasErrors(['title', 'city', 'start_at']);
    }

    public function test_event_end_cannot_precede_start(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), [
                'title' => 'Bad Dates',
                'city' => 'Tokyo',
                'start_at' => '2026-12-10 10:00:00',
                'end_at' => '2026-12-09 10:00:00',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('end_at');
    }

    public function test_event_validates_coordinates_and_ticket_url(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), [
                'title' => 'Bad Geo',
                'city' => 'Paris',
                'start_at' => '2026-12-10 10:00:00',
                'latitude' => 200,
                'longitude' => -200,
                'ticket_url' => 'not-a-url',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors(['latitude', 'longitude', 'ticket_url']);
    }

    public function test_admin_can_update_and_cancel_event(): void
    {
        $event = Event::create([
            'title' => 'Original',
            'city' => 'Berlin',
            'start_at' => '2026-12-01 10:00:00',
            'status' => 'published',
        ]);

        $this->actingAs($this->admin)->put(route('admin.events.update', $event), [
            'title' => 'Updated',
            'city' => 'Berlin',
            'start_at' => '2026-12-01 10:00:00',
            'status' => 'cancelled',
        ])->assertRedirect(route('admin.events.index'));

        $event->refresh();
        $this->assertSame('Updated', $event->title);
        $this->assertSame('cancelled', $event->status);
    }

    public function test_cancelled_events_remain_visible_to_admins(): void
    {
        Event::create([
            'title' => 'Cancelled Show',
            'city' => 'Osaka',
            'start_at' => '2026-12-01 10:00:00',
            'status' => 'cancelled',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.events.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee('Cancelled Show');
    }

    public function test_admin_can_delete_event(): void
    {
        $event = Event::create([
            'title' => 'Doomed',
            'city' => 'Rome',
            'start_at' => '2026-12-01 10:00:00',
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.events.destroy', $event))
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_non_admin_cannot_manage_events(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.events.index'))->assertRedirect(route('user.dashboard'));
    }
}
