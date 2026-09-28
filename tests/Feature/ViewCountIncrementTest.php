<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use App\Models\MerchandiseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ViewCountIncrementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Each test starts from a clean dedup cache so the dedup assertions
        // only measure the behaviour under test rather than leftover keys.
        Cache::flush();
    }

    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'anime'], ['name' => 'Anime']);
    }

    private function content(array $attributes = []): Content
    {
        return Content::create(array_merge([
            'title' => 'Viewable story', 'slug' => 'viewable-story',
            'category_id' => $this->category()->id,
            'type' => 'article', 'status' => 'published',
        ], $attributes));
    }

    private function merchandise(array $attributes = []): MerchandiseItem
    {
        $category = $this->category();
        $story = $attributes['content_id'] ?? Content::create([
            'title' => 'Story for figure', 'slug' => 'story-for-figure',
            'category_id' => $category->id, 'type' => 'article', 'status' => 'published',
        ]);

        return MerchandiseItem::create(array_merge([
            'name' => 'Viewable figure', 'slug' => 'viewable-figure',
            'category_id' => $category->id,
            'content_id' => $story->id,
        ], $attributes));
    }

    private function event(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Viewable event', 'slug' => 'viewable-event',
            'category_id' => $this->category()->id,
            'status' => 'published', 'event_type' => 'convention',
            'city' => 'Lahore', 'start_at' => now()->addDays(3),
        ], $attributes));
    }

    public function test_content_detail_increments_view_count_for_guests(): void
    {
        $content = $this->content();

        $this->get(route('public.content', $content->slug))->assertOk();
        $this->assertSame(1, $content->fresh()->view_count);

        // Refreshing the same page in the same session must NOT inflate the count.
        $this->get(route('public.content', $content->slug))->assertOk();
        $this->assertSame(1, $content->fresh()->view_count);

        // Simulate a fresh viewer (new browser / cleared cache).
        Cache::flush();
        $this->get(route('public.content', $content->slug))->assertOk();
        $this->assertSame(2, $content->fresh()->view_count);
    }

    public function test_content_detail_increments_view_count_for_authenticated_users(): void
    {
        $user = \App\Models\User::factory()->create();
        $content = $this->content();

        $this->actingAs($user)->get(route('public.content', $content->slug))->assertOk();
        $this->assertSame(1, $content->fresh()->view_count);

        // Same user revisiting inside the dedup window must not bump again.
        $this->actingAs($user)->get(route('public.content', $content->slug))->assertOk();
        $this->assertSame(1, $content->fresh()->view_count);

        $other = \App\Models\User::factory()->create();
        $this->actingAs($other)->get(route('public.content', $content->slug))->assertOk();
        $this->assertSame(2, $content->fresh()->view_count);
    }

    public function test_fan_content_modal_and_full_page_increment_view_count(): void
    {
        $creator = \App\Models\User::factory()->create();
        $content = $this->content([
            'slug' => 'fan-creation',
            'title' => 'Fan creation',
            'is_user_submitted' => true,
            'submitted_by' => $creator->id,
        ]);
        $url = route('public.fan-content.show', $content->slug);

        $this->get($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame(1, $content->fresh()->view_count);

        // Switching from modal to full page in the same session is still one view.
        $this->get($url)->assertOk();
        $this->assertSame(1, $content->fresh()->view_count);

        // A fresh viewer (modal) should add a separate view.
        Cache::flush();
        $this->get($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertSame(2, $content->fresh()->view_count);
    }

    public function test_merchandise_detail_increments_view_count(): void
    {
        $item = $this->merchandise();

        $this->get(route('public.merchandise', $item->slug))->assertOk();
        $this->assertSame(1, $item->fresh()->view_count);

        $this->get(route('public.merchandise', $item->slug))->assertOk();
        $this->assertSame(1, $item->fresh()->view_count);

        Cache::flush();
        $this->get(route('public.merchandise', $item->slug))->assertOk();
        $this->assertSame(2, $item->fresh()->view_count);
    }

    public function test_event_detail_increments_view_count(): void
    {
        $event = $this->event();

        $this->get(route('events.show', $event->slug))->assertOk();
        $this->assertSame(1, $event->fresh()->view_count);

        $this->get(route('events.show', $event->slug))->assertOk();
        $this->assertSame(1, $event->fresh()->view_count);

        Cache::flush();
        $this->get(route('events.show', $event->slug))->assertOk();
        $this->assertSame(2, $event->fresh()->view_count);
    }

    public function test_view_count_increment_does_not_create_activity_log(): void
    {
        $content = $this->content();

        $this->get(route('public.content', $content->slug))->assertOk();
        Cache::flush();
        $this->get(route('public.content', $content->slug))->assertOk();

        $this->assertSame(0, ActivityLog::where('event', 'updated')
            ->where('auditable_type', (new Content)->getMorphClass())->count());
        $this->assertSame(1, ActivityLog::where('event', 'created')
            ->where('auditable_type', (new Content)->getMorphClass())->count());
        $this->assertSame(2, $content->fresh()->view_count);
    }

    public function test_draft_content_cannot_be_viewed_or_increment_count(): void
    {
        $content = $this->content(['status' => 'draft']);

        $this->get(route('public.content', $content->slug))->assertNotFound();
        $this->assertSame(0, $content->fresh()->view_count);
    }

    public function test_unpublished_event_cannot_be_viewed_or_increment_count(): void
    {
        $event = $this->event(['status' => 'draft']);

        $this->get(route('events.show', $event->slug))->assertNotFound();
        $this->assertSame(0, $event->fresh()->view_count);
    }
}