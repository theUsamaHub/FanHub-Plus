<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Category;
use App\Models\Event;
use App\Models\Media;
use App\Services\HomepageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomeEventsMultimediaTest extends TestCase
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
            'title' => 'Gathering '.Event::count(), 'status' => 'published',
            'start_at' => now()->addDay(), 'end_at' => now()->addDays(2),
            'city' => 'Lahore', 'venue' => 'Expo Centre', 'event_type' => 'gaming',
            'short_description' => 'A gathering from the database.',
        ], $attributes));
    }

    private function content(array $attributes = []): Content
    {
        return Content::create(array_merge([
            'category_id' => Category::firstOrCreate(['slug' => 'anime'], ['name' => 'Anime'])->id,
            'title' => 'Artwork '.Content::count(), 'type' => 'image',
            'status' => 'published', 'published_at' => now()->subHour(),
        ], $attributes));
    }

    private function image(string $path): Media
    {
        return Media::create([
            'disk' => 'public', 'path' => $path, 'original_filename' => 'art.jpg',
            'mime_type' => 'image/jpeg', 'media_type' => 'image', 'size_bytes' => 100,
        ]);
    }

    public function test_home_selects_five_published_active_events_and_keeps_details_in_sync_markup(): void
    {
        $featured = $this->event(['title' => 'Featured gathering', 'is_featured' => true, 'start_at' => now()->addWeek(), 'end_at' => now()->addWeek()->addDay()]);
        $ongoing = $this->event(['title' => 'Ongoing gathering', 'start_at' => now()->subHour(), 'end_at' => now()->addHour()]);
        for ($i = 0; $i < 6; $i++) $this->event(['start_at' => now()->addDays($i + 1)]);
        $this->event(['title' => 'Draft gathering', 'status' => 'draft', 'is_featured' => true]);
        $this->event(['title' => 'Cancelled gathering', 'status' => 'cancelled']);
        $this->event(['title' => 'Past gathering', 'start_at' => now()->subDays(2), 'end_at' => now()->subDay()]);

        $events = app(HomepageService::class)->sections()['homeEvents'];
        $this->assertCount(5, $events);
        $this->assertSame($featured->id, $events->first()->id);
        $this->assertSame($ongoing->id, $events[1]->id);
        $response = $this->get('/')->assertOk()->assertSee('Featured gathering')->assertSee('Lahore')
            ->assertSee('A gathering from the database.')->assertSee(route('events.show', $featured->slug))
            ->assertDontSee('Draft gathering')->assertDontSee('Cancelled gathering')->assertDontSee('Past gathering');
        $this->assertSame(5, substr_count($response->getContent(), 'data-event-select='));
        $this->assertSame(5, substr_count($response->getContent(), 'data-event-panel aria-hidden='));
    }

    public function test_event_edits_invalidate_the_homepage_cache(): void
    {
        $event = $this->event(['title' => 'Live gathering']);
        $this->get('/')->assertSee('Live gathering');
        $event->update(['title' => 'Renamed gathering']);
        $this->get('/')->assertSee('Renamed gathering')->assertDontSee('Live gathering');
        $event->update(['status' => 'draft']);
        $this->get('/')->assertDontSee('Renamed gathering');
    }

    public function test_multimedia_uses_public_content_and_does_not_expose_unpublished_uploads(): void
    {
        $visible = $this->content(['title' => 'Public fan art']);
        $video = $this->content(['title' => 'Public trailer', 'type' => 'video']);
        $this->content(['title' => 'Draft art', 'status' => 'draft']);
        $this->content(['title' => 'Scheduled art', 'published_at' => now()->addDay()]);
        $this->content(['title' => 'Pending art', 'status' => 'pending_review']);
        $this->content(['title' => 'Text article', 'type' => 'article']);
        $this->image('private/unpublished.jpg');

        $items = app(HomepageService::class)->sections()['multimediaItems'];
        $this->assertEqualsCanonicalizing([$visible->id, $video->id], $items->modelKeys());
        $response = $this->get('/')->assertOk()->assertSee('Public fan art')->assertSee('Public trailer')
            ->assertSee(route('public.content', $visible->slug))->assertDontSee('Draft art')
            ->assertDontSee('Scheduled art')->assertDontSee('Pending art')->assertDontSee('private/unpublished.jpg');
        $this->assertSame(2, substr_count($response->getContent(), 'data-media-row aria-label='));
    }

    public function test_section_images_prefer_uploads_and_use_requested_fandom_fallbacks(): void
    {
        $event = $this->event();
        $art = $this->content();
        $this->get('/')->assertOk()->assertSee('src="'.asset('images/fandoms/gaming.png').'"', false)
            ->assertSee('src="'.asset('images/fandoms/anime.png').'"', false);

        $cover = $this->image('covers/event.jpg');
        $event->update(['cover_media_id' => $cover->id]);
        $image = $this->image('gallery/art.jpg');
        $art->media()->attach($image, ['role' => 'gallery', 'sort_order' => 0]);
        $art->touch();
        $this->get('/')->assertOk()->assertSee('src="'.$cover->url.'"', false)->assertSee('src="'.$image->url.'"', false);

        $cover->update(['path' => '0']);
        $image->update(['path' => '']);
        $this->get('/')->assertOk()->assertDontSee('src=""', false)
            ->assertSee('src="'.asset('images/fandoms/gaming.png').'"', false);
    }

    public function test_empty_and_single_record_sections_render_without_fabricating_records(): void
    {
        $this->get('/')->assertOk()->assertSee('New events are on their way.')
            ->assertSee('Fan art, videos, and more will appear here when published.');
        $this->event();
        $this->content();
        $response = $this->get('/')->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'data-event-select='));
        $this->assertSame(2, substr_count($response->getContent(), 'data-media-row aria-label='));
    }
}
