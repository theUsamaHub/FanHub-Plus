<?php

namespace Tests\Feature;

use App\Models\{Category, Content, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FanContentTest extends TestCase
{
    use RefreshDatabase;

    private function creation(array $attributes = []): Content
    {
        return Content::create(array_merge([
            'title' => 'Fan creation', 'category_id' => Category::firstOrCreate(['slug' => 'anime'], ['name' => 'Anime'])->id,
            'type' => 'article', 'status' => 'published', 'is_user_submitted' => true,
            'published_at' => now()->subDay(), 'body' => 'A creation by a fan.',
        ], $attributes));
    }

    public function test_home_links_to_fan_listing_and_listing_excludes_non_public_and_editorial_content(): void
    {
        $fan = $this->creation();
        foreach (['draft', 'pending_review', 'rejected'] as $status) $this->creation(['title' => 'Hidden '.$status, 'status' => $status]);
        $this->creation(['title' => 'Scheduled fan', 'published_at' => now()->addDay()]);
        $this->creation(['title' => 'Editorial story', 'is_user_submitted' => false]);
        $this->get('/')->assertOk()->assertSee(route('public.fan-content.index'))
            ->assertSee('home-multimedia__view-all', false)->assertDontSee('fan-home-title', false);
        $this->get(route('public.fan-content.index'))->assertOk()->assertSee($fan->title)
            ->assertDontSee('Hidden draft')->assertDontSee('Hidden pending_review')->assertDontSee('Hidden rejected')
            ->assertDontSee('Scheduled fan')->assertDontSee('Editorial story');
    }

    public function test_modal_endpoint_returns_only_requested_public_fan_content_and_sanitizes_body(): void
    {
        $creator = User::factory()->create(['name' => 'Fan artist']);
        $fan = $this->creation(['submitted_by' => $creator->id, 'body' => '<p>My original story</p><script>alert(1)</script>']);
        $this->creation(['title' => 'Different creation']);
        $url = route('public.fan-content.show', $fan->slug);
        $this->get($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSee('My original story')
            ->assertSee('Fan artist')->assertDontSee('<script>', false)->assertDontSee('Different creation')->assertDontSee('<!DOCTYPE', false);
        $this->get($url)->assertOk()->assertSee('All fan content');
        foreach (['draft', 'pending_review', 'rejected'] as $status) {
            $fan->update(['status' => $status]);
            $this->get($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertNotFound();
        }
        $fan->update(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->get($url)->assertNotFound();
        $fan->update(['published_at' => now()->subDay(), 'is_user_submitted' => false]);
        $this->get($url)->assertNotFound();
    }

    public function test_filters_and_pagination_keep_fan_content_scoped(): void
    {
        for ($i = 0; $i < 13; $i++) $this->creation(['title' => 'Artwork '.$i, 'type' => 'image']);
        $this->creation(['title' => 'Other fan article']);
        $this->get(route('public.fan-content.index', ['category' => 'anime', 'type' => 'image', 'q' => 'Artwork']))
            ->assertOk()->assertViewHas('contents', fn ($items) => $items->total() === 13 && $items->count() === 12)
            ->assertDontSee('Other fan article')->assertSee('page=2');
        $this->get(route('public.fan-content.index', ['type' => 'image', 'page' => 2]))->assertOk()
            ->assertViewHas('contents', fn ($items) => $items->count() === 1);
        $this->get(route('public.fan-content.index', ['q' => '%']))->assertOk()->assertSee('No creations found');
    }
}
