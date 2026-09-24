<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Content;
use App\Models\Media;
use App\Models\MerchandiseItem;
use App\Models\User;
use App\Services\HomepageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomepageSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-24 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function story(Category $category, array $attributes = []): Content
    {
        return Content::create(array_merge([
            'category_id' => $category->id, 'title' => 'Story '.Content::count(),
            'type' => 'article', 'status' => 'published', 'published_at' => now()->subDay(),
            'popularity_score' => 50, 'body' => str_repeat('story ', 450), 'excerpt' => 'An excerpt from the database.',
        ], $attributes));
    }

    public function test_ranked_and_featured_content_is_bounded_and_database_driven(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $author = User::factory()->create(['name' => 'Published Author']);
        for ($i = 0; $i < 9; $i++) {
            $this->story($category, ['title' => 'Ranked '.$i, 'popularity_score' => 90 - $i, 'is_featured' => true, 'submitted_by' => $author->id]);
        }
        $this->story($category, ['title' => 'Secret draft', 'status' => 'draft', 'popularity_score' => 999, 'is_featured' => true]);
        $this->story($category, ['title' => 'Scheduled story', 'published_at' => now()->addHour(), 'popularity_score' => 998, 'is_featured' => true]);

        $data = app(HomepageService::class)->sections();
        $this->assertCount(6, $data['trending']);
        $this->assertCount(3, $data['featuredStories']);
        $this->assertSame('Ranked 0', $data['trending']->first()->title);
        $this->get('/')->assertOk()->assertSeeInOrder(['Ranked 0', 'Ranked 1', 'Ranked 2'])
            ->assertSee('Published Author')->assertSee('3 min read')->assertSee('An excerpt from the database.')
            ->assertDontSee('Secret draft')->assertDontSee('Scheduled story')->assertDontSee('Ranked 8');
    }

    public function test_cards_use_uploaded_covers_and_local_fallback_artwork(): void
    {
        $category = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $story = $this->story($category, ['is_featured' => true, 'release_date' => today()->addDay()]);
        $this->assertSame(asset('images/fandoms/gaming.png'), $story->artwork_url);
        foreach (array_unique(config('homepage.artwork')) as $path) {
            $this->assertFileExists(public_path($path));
        }

        $cover = Media::create(['disk' => 'public', 'path' => 'covers/game.jpg', 'original_filename' => 'game.jpg', 'mime_type' => 'image/jpeg', 'media_type' => 'image', 'size_bytes' => 100]);
        $story->media()->attach($cover, ['role' => 'cover', 'sort_order' => 0]);
        $this->assertSame($cover->url, $story->fresh()->artwork_url);
        $this->assertSame($cover->url, app(HomepageService::class)->releases()->first()['image']);
        $this->get('/')->assertOk()->assertSee($cover->url);
        $this->get(route('public.content', $story->slug))->assertOk()->assertSee($cover->url);
    }

    public function test_featured_stories_are_articles_and_cache_refreshes_after_edits(): void
    {
        $category = Category::create(['name' => 'Movies', 'slug' => 'movies']);
        $story = $this->story($category, ['title' => 'Published feature', 'is_featured' => true]);
        $this->story($category, ['title' => 'Featured video', 'type' => 'video', 'is_featured' => true]);
        $this->assertCount(1, app(HomepageService::class)->sections()['featuredStories']);
        $this->get('/')->assertSee('Published feature');
        $story->update(['status' => 'draft']);
        $this->get('/')->assertDontSee('Published feature');
        $this->get(route('public.content', $story->slug))->assertNotFound();
    }

    public function test_upcoming_dates_merge_with_merchandise_and_unknown_dates_are_honest(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $this->story($category, ['title' => 'Next month', 'release_date' => today()->addDays(20), 'release_label' => 'Special premiere']);
        $this->story($category, ['title' => 'Already released', 'release_date' => today()->subDay()]);
        MerchandiseItem::create(['category_id' => $category->id, 'name' => 'First collectible', 'tag' => 'limited_edition', 'is_upcoming' => true, 'release_date' => today()->addDay()]);
        MerchandiseItem::create(['category_id' => $category->id, 'name' => 'Undated collectible', 'tag' => 'pre_order', 'is_upcoming' => true]);
        $releases = app(HomepageService::class)->releases();
        $this->assertSame(['First collectible', 'Next month', 'Undated collectible'], $releases->pluck('title')->all());
        $this->get('/?release_category=merchandise')->assertOk()->assertSee('Date TBA')->assertSee('limited edition')->assertSee('Undated collectible');
        $this->assertCount(1, app(HomepageService::class)->releases('anime'));
        $this->get('/?release_category=anime')->assertSee('Special premiere');
    }

    public function test_filters_query_beyond_the_initial_six_and_view_all_is_paginated(): void
    {
        $anime = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $gaming = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        for ($i = 0; $i < 14; $i++) {
            $this->story($anime, ['title' => 'Release '.$i, 'release_date' => today()->addDays($i + 1)]);
        }
        $this->story($gaming, ['title' => 'Distant game', 'release_date' => today()->addYear()]);
        $this->assertNotContains('Distant game', app(HomepageService::class)->releases()->pluck('title'));
        $this->get('/?release_category=gaming')->assertOk()->assertSee('Distant game')->assertSee('2027');
        $this->get('/discover/upcoming?category=anime')->assertOk()->assertSee('Page 1 of 2');
        $this->get('/discover/upcoming?category=anime&page=2')->assertOk()->assertSee('Release 13')->assertDontSee('Distant game');
        $this->get('/?release_category=invalid')->assertNotFound();
    }

    public function test_guests_and_users_get_the_same_sections_and_public_detail(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $story = $this->story($category, ['title' => 'Shared story', 'is_featured' => true, 'body' => '<script>alert("unsafe")</script><p>Public story body</p>']);
        $this->get('/')->assertOk()->assertSee('Shared story');
        $this->actingAs(User::factory()->create())->get('/')->assertOk()->assertSee('Shared story')->assertSee('Logout');
        $this->get(route('public.content', $story->slug))->assertOk()->assertSee('Public story body')->assertDontSee('<script>alert(', false);
    }

    public function test_empty_database_provides_three_readable_empty_states(): void
    {
        $this->get('/')->assertOk()->assertSee('Trending stories will appear here')
            ->assertSee('Featured stories will appear here soon')->assertSee('No upcoming releases announced');
        $this->get('/discover/upcoming')->assertOk()->assertSee('No upcoming releases announced yet.');
    }
}
