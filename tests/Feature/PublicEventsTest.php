<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-25 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function event(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Fan event '.Event::count(), 'city' => 'Lahore', 'venue' => 'Expo Centre',
            'description' => 'Meet fellow fans at a celebration of stories.',
            'start_at' => now()->addDays(3), 'end_at' => now()->addDays(3)->addHours(4),
            'status' => 'published', 'event_type' => 'convention',
        ], $attributes));
    }

    public function test_featured_selection_is_bounded_and_grid_is_paginated_without_duplicates(): void
    {
        for ($i = 0; $i < 20; $i++) $this->event(['is_featured' => $i < 6, 'popularity_score' => 100 - $i]);
        $response = $this->get('/events')->assertOk();
        $this->assertCount(4, $response['featured']);
        $this->assertCount(12, $response['events']);
        $this->assertSame(16, $response['events']->total());
        $this->assertEmpty(array_intersect($response['featured']->modelKeys(), $response['events']->getCollection()->modelKeys()));
        $page2 = $this->get('/events?page=2')->assertOk()->assertSee('Page')->assertSee('of 2');
        $this->assertCount(0, $page2['featured']);
        $this->assertCount(4, $page2['events']);
        $this->assertSame(16, $page2['events']->total());
    }

    public function test_filters_search_all_events_including_featured_and_sort_popularity(): void
    {
        $anime = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $game = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $match = $this->event(['title' => 'Anime 100% Gathering', 'category_id' => $anime->id, 'is_featured' => true, 'popularity_score' => 80]);
        $this->event(['title' => 'Anime anything Gathering', 'category_id' => $anime->id, 'popularity_score' => 10]);
        $this->event(['category_id' => $game->id, 'city' => 'Karachi', 'event_type' => 'gaming']);
        $this->event(['title' => 'Past show', 'start_at' => now()->subDays(3), 'end_at' => now()->subDay()]);
        $response = $this->get('/events?category=anime&city=Lahore&type=convention&date=2026-09-28&when=upcoming&sort=popular')->assertOk()->assertSee($match->title)->assertDontSee('Past show');
        $this->assertCount(0, $response['featured']);
        $this->assertCount(2, $response['events']);
        $this->assertSame($match->id, $response['events']->first()->id);
        $this->get('/events?q=100%25')->assertOk()->assertSee($match->title)->assertDontSee('Anime anything Gathering');
        $this->get('/events?q=0')->assertOk()->assertSee($match->title)->assertDontSee('Anime anything Gathering');
        $this->get('/events?when=past')->assertOk()->assertSee('Past show')->assertDontSee($match->title);
        $this->get('/events?date=2026-09-29')->assertOk()->assertSee('No events found this time.');
        $this->from('/events')->get('/events?date=invalid&type=invalid')->assertRedirect('/events')->assertSessionHasErrors(['date', 'type']);
    }

    public function test_drafts_and_cancelled_events_never_leak_to_public_routes(): void
    {
        foreach (['draft', 'cancelled'] as $status) {
            $event = $this->event(['title' => 'Private '.$status, 'status' => $status, 'is_featured' => true]);
            $this->get('/events')->assertOk()->assertDontSee($event->title);
            $this->get(route('events.show', $event->slug))->assertNotFound();
            $this->get(route('events.calendar', $event->slug))->assertNotFound();
        }
        $this->get('/events/nonexistent')->assertNotFound();
        $this->get('/discover/events')->assertRedirect('/events');
    }

    public function test_detail_renders_database_metadata_gallery_safe_links_and_fallback(): void
    {
        $category = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $event = $this->event(['category_id' => $category->id, 'latitude' => 0, 'longitude' => 0, 'ticket_url' => 'javascript:alert(1)', 'description' => '<script>alert(1)</script>']);
        $related = $this->event(['category_id' => $category->id, 'title' => 'Related gaming event']);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee(asset('images/fandoms/gaming.png'))
            ->assertSee('Expo Centre')->assertSee('Lahore')->assertSee('UTC')->assertSee($related->title)
            ->assertSee('query=0.00000000%2C0.00000000')->assertDontSee('href="javascript:', false)->assertDontSee('<script>alert(1)</script>', false);
        $photo = Media::create(['disk' => 'public', 'path' => 'events/cover.jpg', 'original_filename' => 'cover.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'size_bytes' => 100]);
        $event->update(['cover_media_id' => $photo->id]);
        $event->galleryMedia()->attach($photo);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee($photo->url)->assertSee('From this universe.');
        $this->assertTrue($photo->isReferenced());
    }

    public function test_calendar_download_preserves_utc_dates_and_escapes_lines(): void
    {
        $event = $this->event(['title' => 'Fans, stories; together', 'description' => "Meet fans\nBEGIN:BAD"]);
        $response = $this->get(route('events.calendar', $event->slug))->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertSee('DTSTART:20260928T120000Z', false)->assertSee('DTEND:20260928T160000Z', false)
            ->assertSee('SUMMARY:Fans\\, stories\\; together', false)->assertSee('DESCRIPTION:Meet fans\\nBEGIN:BAD', false);
        $this->assertStringNotContainsString("\r\nBEGIN:BAD", $response->getContent());
    }

    public function test_slugs_are_unique_stable_and_accessible_to_guests_and_members(): void
    {
        $first = $this->event(['title' => 'A fan gathering']);
        $second = $this->event(['title' => 'A fan gathering']);
        $this->assertNotSame($first->slug, $second->slug);
        $slug = $first->slug;
        $first->update(['title' => 'Updated title']);
        $this->assertSame($slug, $first->fresh()->slug);
        $this->get(route('events.show', $slug))->assertOk();
        $this->actingAs(User::factory()->create())->get('/events')->assertOk()->assertSee('Updated title');
    }

    public function test_admin_can_manage_featured_type_gallery_and_edit_form(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'Admin', 'slug' => 'admin']));
        $image = Media::create(['disk' => 'public', 'path' => 'gallery.jpg', 'original_filename' => 'gallery.jpg', 'media_type' => 'image', 'mime_type' => 'image/jpeg', 'size_bytes' => 100]);
        $data = ['title' => 'Editorial pick', 'city' => 'Lahore', 'start_at' => '2026-10-01 12:00', 'status' => 'published', 'is_featured' => 1, 'event_type' => 'meetup', 'popularity_score' => 95, 'gallery_present' => 1, 'gallery_media_ids' => [$image->id]];
        $this->actingAs($admin)->post(route('admin.events.store'), $data)->assertRedirect(route('admin.events.index'));
        $event = Event::first();
        $this->assertTrue($event->is_featured);
        $this->assertCount(1, $event->galleryMedia);
        $this->get(route('admin.events.create'))->assertOk()->assertSee('Featured event');
        $this->get(route('admin.events.edit', $event))->assertOk()->assertSee('Featured event')->assertSee('gallery.jpg');
        unset($data['gallery_media_ids']);
        $data['is_featured'] = 0;
        $this->put(route('admin.events.update', $event), $data)->assertRedirect();
        $this->assertCount(0, $event->fresh()->galleryMedia);
        $this->assertFalse($event->fresh()->is_featured);
    }
}
