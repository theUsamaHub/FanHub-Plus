<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyEventsTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $title, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => $title, 'city' => 'Test city', 'start_at' => now()->addDay(),
            'status' => 'published', 'latitude' => 0, 'longitude' => 0,
        ], $attributes));
    }

    public function test_default_radius_distance_visibility_and_missing_coordinates(): void
    {
        $this->event('At origin');
        $this->event('Inside radius', ['latitude' => .04, 'is_featured' => true]);
        $this->event('Outside radius', ['latitude' => .05]);
        $this->event('No coordinates', ['latitude' => null]);
        $this->event('Invalid coordinates', ['latitude' => 91]);
        $this->event('Invalid longitude', ['longitude' => 181]);
        $this->event('Draft event', ['status' => 'draft']);
        $this->event('Cancelled event', ['status' => 'cancelled']);
        $this->event('Past published event', ['start_at' => now()->subDay()]);

        $response = $this->postJson(route('events.nearby.search'), ['latitude' => 0, 'longitude' => 0])
            ->assertOk()->assertJsonPath('total', 3)->assertHeader('Cache-Control', 'no-store, private');
        $html = $response->json('html');
        foreach (['At origin', 'Inside radius', 'Past published event', '4.4 km away', '0.0 km away'] as $text) $this->assertStringContainsString($text, $html);
        foreach (['Outside radius', 'No coordinates', 'Invalid coordinates', 'Invalid longitude', 'Draft event', 'Cancelled event'] as $text) $this->assertStringNotContainsString($text, $html);
        $this->get('/events')->assertOk()->assertSee('No coordinates')->assertSee('Outside radius')->assertSee('Show Nearby Events');
        $this->postJson(route('events.nearby.search'), ['latitude' => 0, 'longitude' => 0, 'radius' => 10])
            ->assertOk()->assertJsonPath('total', 4);
    }

    public function test_filters_and_pagination_are_preserved(): void
    {
        for ($i = 0; $i < 14; $i++) $this->event('Local event '.$i);
        $this->event('Different city', ['city' => 'Elsewhere']);
        $data = ['latitude' => 0, 'longitude' => 0, 'city' => 'Test city'];
        $first = $this->postJson(route('events.nearby.search'), $data)->assertOk()->assertJsonPath('total', 14);
        $second = $this->postJson(route('events.nearby.search'), [...$data, 'page' => 2])->assertOk();
        $this->assertSame(12, substr_count($first->json('html'), '<article class="event-card"'));
        $this->assertSame(2, substr_count($second->json('html'), '<article class="event-card"'));
        $this->assertStringContainsString('Local event 13', $second->json('html'));
        $this->assertStringNotContainsString('Different city', $first->json('html'));
        $this->assertStringNotContainsString('latitude=', $first->json('html'));
    }

    public function test_validation_rejects_missing_invalid_coordinates_and_unapproved_radii(): void
    {
        foreach ([[], ['latitude' => 0], ['latitude' => 91, 'longitude' => 181],
            ['latitude' => 'NaN', 'longitude' => 0], ['latitude' => 0, 'longitude' => 0, 'radius' => 500],
            ['latitude' => 0, 'longitude' => 0, 'radius' => '5 OR 1=1']] as $data) {
            $this->postJson(route('events.nearby.search'), $data)->assertUnprocessable();
        }
    }

    public function test_distance_handles_date_line_and_poles(): void
    {
        $this->event('Across date line', ['latitude' => 0, 'longitude' => -179.99]);
        $this->event('At north pole', ['latitude' => 90, 'longitude' => 120]);
        $this->postJson(route('events.nearby.search'), ['latitude' => 0, 'longitude' => 179.99])
            ->assertOk()->assertJsonPath('total', 1);
        $this->postJson(route('events.nearby.search'), ['latitude' => 90, 'longitude' => -60])
            ->assertOk()->assertJsonPath('total', 1);
    }

    public function test_configured_map_url_is_saved_and_safely_rendered(): void
    {
        $event = $this->event('Mapped event', ['google_maps_location' => 'https://maps.google.com/?q=venue']);
        $this->assertSame('https://maps.google.com/?q=venue', $event->fresh()->map_url);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('https://maps.google.com/?q=venue');
        $event->update(['google_maps_location' => 'javascript:alert(1)']);
        $this->assertStringStartsWith('https://www.google.com/maps/', $event->fresh()->map_url);
    }
}
