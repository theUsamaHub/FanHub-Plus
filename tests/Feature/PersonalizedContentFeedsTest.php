<?php

namespace Tests\Feature;

use App\Models\{Category, CharacterProfile, Content, Event, MerchandiseItem, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalizedContentFeedsTest extends TestCase
{
    use RefreshDatabase;

    private function story(Category $category, string $title, array $attributes = []): Content
    {
        return Content::create(array_merge([
            'category_id' => $category->id, 'title' => $title, 'type' => 'article',
            'status' => 'published', 'published_at' => now()->subDay(),
        ], $attributes));
    }

    public function test_favorites_rank_before_pagination_and_other_content_remains_accessible(): void
    {
        $favorite = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $other = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $user = User::factory()->create();
        $user->favoriteCategories()->attach($favorite);
        $preferred = $this->story($favorite, 'Older favorite', ['published_at' => now()->subYear()]);
        for ($i = 0; $i < 13; $i++) $this->story($other, 'General story '.$i);
        $this->story($favorite, 'Hidden draft', ['status' => 'draft']);
        $this->story($favorite, 'Scheduled story', ['published_at' => now()->addDay()]);

        $first = $this->actingAs($user)->get('/explore?type=article')->assertOk()->viewData('contents');
        $second = $this->get('/explore?type=article&page=2')->assertOk()->viewData('contents');
        $this->assertSame($preferred->id, $first->first()->id);
        $this->assertSame(14, $first->total());
        $this->assertCount(2, $second);
        $this->assertCount(14, $first->getCollection()->concat($second->getCollection())->unique('id'));
        $this->assertStringContainsString('type=article', $first->nextPageUrl());
        $this->get('/explore?category=gaming')->assertOk()->assertViewHas('contents',
            fn ($items) => $items->total() === 13 && $items->every(fn ($item) => $item->category_id === $other->id));
        $this->get('/explore?q=Older')->assertOk()->assertViewHas('contents', fn ($items) => $items->total() === 1);
    }

    public function test_guests_empty_preferences_and_empty_favorite_categories_keep_default_results(): void
    {
        $category = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $empty = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $old = $this->story($category, 'Older', ['published_at' => now()->subYear()]);
        $new = $this->story($category, 'Newer');
        $expected = [$new->id, $old->id];
        $this->assertSame($expected, $this->get('/explore')->assertOk()->viewData('contents')->pluck('id')->all());
        $user = User::factory()->create();
        $this->assertSame($expected, $this->actingAs($user)->get('/explore')->assertOk()->viewData('contents')->pluck('id')->all());
        $user->favoriteCategories()->attach($empty);
        $this->assertSame($expected, $this->actingAs($user->fresh())->get('/explore')->assertOk()->viewData('contents')->pluck('id')->all());
    }

    public function test_multimedia_and_spotlight_prioritize_favorites_and_keep_other_results(): void
    {
        $favorite = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $other = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $user = User::factory()->create();
        $user->favoriteCategories()->attach($favorite);
        foreach ([$other, $favorite] as $category) {
            $this->story($category, $category->name.' video', ['type' => 'video', 'popularity_score' => $category->is($other) ? 100 : 1]);
            Event::create(['title' => $category->name.' event', 'category_id' => $category->id,
                'city' => 'Karachi', 'status' => 'published', 'is_featured' => true,
                'start_at' => now()->addDay(), 'popularity_score' => $category->is($other) ? 100 : 1]);
        }
        $this->actingAs($user)->get('/discover/multimedia?type=video&sort=popular')->assertOk()
            ->assertViewHas('items', fn ($items) => $items->total() === 2 && $items->first()->category_id === $favorite->id);
        $this->get('/events')->assertOk()->assertViewHas('featured',
            fn ($items) => $items->count() === 2 && $items->first()->category_id === $favorite->id);
        $this->get('/events?sort=popular')->assertOk()->assertViewHas('events',
            fn ($items) => $items->total() === 2 && $items->first()->category_id === $favorite->id);
    }

    public function test_detail_feeds_rank_linked_favorites_without_adding_unrelated_or_unpublished_records(): void
    {
        $favorite = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $other = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $user = User::factory()->create();
        $user->favoriteCategories()->attach($favorite);
        $main = $this->story($other, 'Main story');
        $characters = [];
        foreach ([$other, $favorite] as $category) {
            $character = CharacterProfile::create(['name' => $category->is($other) ? 'A General' : 'Z Favorite', 'category_id' => $category->id]);
            $characters[] = $character;
            $main->characters()->attach($character);
            MerchandiseItem::create(['name' => $category->name.' item', 'category_id' => $category->id,
                'content_id' => $main->id, 'character_id' => $characters[0]->id, 'view_count' => $category->is($other) ? 100 : 1]);
            Event::create(['title' => $category->name.' event', 'category_id' => $category->id,
                'content_id' => $main->id, 'city' => 'Karachi', 'status' => 'published',
                'start_at' => $category->is($other) ? now()->addDay() : now()->addWeek()]);
        }
        Event::create(['title' => 'Draft event', 'category_id' => $favorite->id, 'content_id' => $main->id,
            'city' => 'Karachi', 'status' => 'draft', 'start_at' => now()->addDay()]);
        CharacterProfile::create(['name' => 'Unrelated favorite', 'category_id' => $favorite->id]);
        $response = $this->actingAs($user)->get(route('public.content', $main))->assertOk();
        foreach (['characters', 'merchandise', 'events'] as $feed) {
            $items = $response->viewData($feed);
            $this->assertCount(2, $items);
            $this->assertSame($favorite->id, $items->first()->category_id);
        }
        $preferred = $this->story($favorite, 'Older favorite story', ['published_at' => now()->subYear()]);
        $hidden = $this->story($favorite, 'Draft favorite story', ['status' => 'draft']);
        $characters[0]->contents()->attach([$preferred->id, $hidden->id]);
        $this->story($favorite, 'Unrelated favorite story');
        $response = $this->get(route('public.character', $characters[0]))->assertOk();
        $this->assertSame([$preferred->id, $main->id], $response->viewData('stories')->pluck('id')->all());
        $this->assertSame($favorite->id, $response->viewData('merchandise')->first()->category_id);
    }
}
