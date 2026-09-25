<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\MerchandiseItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeMerchandiseTest extends TestCase
{
    use RefreshDatabase;

    private function item(Category $category, array $attributes = []): MerchandiseItem
    {
        return MerchandiseItem::create(array_merge([
            'category_id' => $category->id, 'name' => 'Collectible '.MerchandiseItem::count(),
            'tag' => 'limited_edition', 'description' => 'Description from the database.',
        ], $attributes));
    }

    public function test_database_cards_and_fallback_are_visible_and_join_is_guest_only(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $item = $this->item($category);
        $this->get('/')->assertOk()->assertSee($item->name)->assertSee('Limited Edition')
            ->assertSee(asset('images/merch-deafult.jpg'))->assertSee('data-join-section', false);
        $this->actingAs(User::factory()->create())->get('/')->assertOk()->assertSee($item->name)
            ->assertDontSee('data-join-section', false);
        $this->assertFileExists(public_path(config('homepage.images.merchandise')));
        $image = Media::create(['disk' => 'public', 'path' => 'merch/photo.jpg', 'original_filename' => 'photo.jpg', 'mime_type' => 'image/jpeg', 'media_type' => 'image', 'size_bytes' => 100]);
        $item->update(['image_media_id' => $image->id]);
        $this->get('/')->assertOk()->assertSee($image->url);
    }

    public function test_filter_queries_beyond_homepage_limit_and_returns_only_requested_partial(): void
    {
        $anime = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $gaming = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $game = $this->item($gaming, ['name' => 'Rare game statue']);
        for ($i = 0; $i < 25; $i++) $this->item($anime);
        $this->get('/')->assertDontSee($game->name);
        $this->get('/?merch_category=gaming', ['X-Home-Section' => 'merchandise'])
            ->assertOk()->assertSee($game->name)->assertDontSee('Collectible 1')->assertDontSee('<html', false);
        $this->get('/?merch_category=invalid')->assertNotFound();
        $this->get('/discover/merchandise?category=anime')->assertOk()->assertSee('Page 1 of 3');
        $this->get('/discover/merchandise?category=cosplay')->assertOk()->assertSee('No merchandise in this fandom yet.');
    }

    public function test_bookmarks_require_login_are_idempotent_and_belong_to_the_current_user(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $item = $this->item($category);
        $url = route('public.merchandise.bookmark', $item->slug);
        $this->postJson($url, ['saved' => true])->assertUnauthorized();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $this->actingAs($first)->postJson($url, ['saved' => true])->assertOk()->assertJson(['saved' => true]);
        $this->postJson($url, ['saved' => true])->assertOk();
        $this->assertDatabaseCount('bookmarks', 1);
        $this->get('/')->assertSee('aria-pressed="true"', false);
        $this->actingAs($second)->postJson($url, ['saved' => false])->assertOk();
        $this->assertDatabaseCount('bookmarks', 1);
        $this->get('/')->assertSee('aria-pressed="false"', false)->assertDontSee('aria-pressed="true"', false);
        $this->actingAs($first)->postJson($url, ['saved' => false])->assertOk()->assertJson(['saved' => false]);
        $this->assertDatabaseCount('bookmarks', 0);
        $this->postJson($url, ['saved' => 'invalid'])->assertUnprocessable();
    }

    public function test_detail_has_real_status_related_items_and_no_commerce_actions(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $item = $this->item($category, ['is_upcoming' => true, 'tag' => 'standard']);
        $related = $this->item($category, ['name' => 'Related figure']);
        $this->get(route('public.merchandise', $item->slug))->assertOk()
            ->assertSee('Description from the database.')->assertSee('Coming Soon')->assertSee('Status: Upcoming')
            ->assertSee('Release date to be announced.')->assertSee($related->name)
            ->assertDontSee('Add to Cart')->assertDontSee('Buy Now')->assertDontSee('Checkout');
    }
}
