<?php

namespace Tests\Feature;

use App\Models\{Category, Event};
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventSlugRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_events_have_detail_links_even_with_model_events_disabled(): void
    {
        Category::create(['name' => 'Anime']);
        Event::withoutEvents(fn () => $this->seed(EventSeeder::class));
        foreach (Event::all() as $event) {
            $this->assertNotEmpty($event->slug);
            $this->get(route('events.show', $event->slug))->assertOk()->assertSee($event->title);
        }
        $response = $this->get('/events')->assertOk();
        foreach (Event::all() as $event) $response->assertSee(route('events.show', $event->slug));
    }

    public function test_repair_preserves_existing_urls_and_resolves_duplicate_titles(): void
    {
        $attributes = ['title' => 'Fan gathering', 'city' => 'Tokyo', 'status' => 'published', 'start_at' => now()->addDay()];
        $existing = Event::create($attributes);
        $missing = Event::withoutEvents(fn () => Event::create($attributes));
        $this->artisan('events:repair-slugs')->assertSuccessful();
        $this->assertSame('fan-gathering', $existing->fresh()->slug);
        $this->assertSame('fan-gathering-2', $missing->fresh()->slug);
        $this->get(route('events.show', $missing->fresh()->slug))->assertOk();
        $this->artisan('events:repair-slugs')->expectsOutput('Repaired 0 event slugs.')->assertSuccessful();
    }
}
