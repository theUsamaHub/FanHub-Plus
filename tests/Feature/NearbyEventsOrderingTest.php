<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyEventsOrderingTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $title, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => $title, 'city' => 'Test city', 'start_at' => now()->addDay(),
            'status' => 'published', 'latitude' => 0, 'longitude' => 0,
        ], $attributes));
    }

    public function test_nearby_events_are_sorted_by_distance(): void
    {
        // Create events at different distances from origin (0, 0)
        // Distance ~0 km
        $this->event('At origin', ['latitude' => 0, 'longitude' => 0, 'start_at' => now()->addDays(3)]);
        
        // Distance ~4.4 km (0.04 degrees lat ≈ 4.4 km)
        $this->event('Close event', ['latitude' => 0.04, 'longitude' => 0, 'start_at' => now()->addDay()]);
        
        // Distance ~11 km (0.1 degrees lat ≈ 11 km)
        $this->event('Far event', ['latitude' => 0.1, 'longitude' => 0, 'start_at' => now()->addDays(2)]);
        
        // Distance ~22 km (0.2 degrees lat ≈ 22 km)
        $this->event('Farther event', ['latitude' => 0.2, 'longitude' => 0, 'start_at' => now()->addHours(5)]);

        $response = $this->postJson(route('events.nearby.search'), ['latitude' => 0, 'longitude' => 0, 'radius' => 50]);
        $response->assertOk();
        $this->assertEquals(4, $response->json('total'), 'Expected 4 events, got: ' . $response->json('total') . ', HTML: ' . $response->json('html'));

        $html = $response->json('html');
        
        // Events should be ordered by distance (closest first)
        // Find positions of each event in the HTML
        $posAtOrigin = strpos($html, 'At origin');
        $posClose = strpos($html, 'Close event');
        $posFar = strpos($html, 'Far event');
        $posFarther = strpos($html, 'Farther event');

        $this->assertLessThan($posClose, $posAtOrigin, 'At origin should appear before Close event');
        $this->assertLessThan($posFar, $posClose, 'Close event should appear before Far event');
        $this->assertLessThan($posFarther, $posFar, 'Far event should appear before Farther event');
    }

    public function test_nearby_events_with_sort_popular_still_sorts_by_popularity(): void
    {
        $this->event('Popular but far', ['latitude' => 0.2, 'longitude' => 0, 'popularity_score' => 100]);
        $this->event('Not popular but close', ['latitude' => 0.01, 'longitude' => 0, 'popularity_score' => 10]);

        $response = $this->postJson(route('events.nearby.search'), [
            'latitude' => 0, 
            'longitude' => 0,
            'sort' => 'popular',
            'radius' => 50
        ]);
        $response->assertOk();
        $this->assertEquals(2, $response->json('total'), 'Expected 2 events, got: ' . $response->json('total') . ', HTML: ' . $response->json('html'));

        $html = $response->json('html');
        
        // With sort=popular, should sort by popularity_score desc
        $posPopular = strpos($html, 'Popular but far');
        $posNotPopular = strpos($html, 'Not popular but close');
        
        $this->assertLessThan($posNotPopular, $posPopular, 'Popular event should appear first when sort=popular');
    }

    public function test_nearby_events_with_sort_soonest_sorts_by_start_date(): void
    {
        $this->event('Later but close', ['latitude' => 0.01, 'longitude' => 0, 'start_at' => now()->addDays(3)]);
        $this->event('Sooner but farther', ['latitude' => 0.1, 'longitude' => 0, 'start_at' => now()->addDay()]);

        $response = $this->postJson(route('events.nearby.search'), [
            'latitude' => 0, 
            'longitude' => 0,
            'sort' => 'soonest',
            'radius' => 50
        ]);
        $response->assertOk();
        $this->assertEquals(2, $response->json('total'), 'Expected 2 events, got: ' . $response->json('total') . ', HTML: ' . $response->json('html'));

        $html = $response->json('html');
        
        // With sort=soonest (default), should sort by start_at
        $posSooner = strpos($html, 'Sooner but farther');
        $posLater = strpos($html, 'Later but close');
        
        $this->assertLessThan($posLater, $posSooner, 'Sooner event should appear first when sort=soonest');
    }
}