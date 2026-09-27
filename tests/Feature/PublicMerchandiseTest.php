<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Content;
use App\Models\MerchandiseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMerchandiseTest extends TestCase
{
    use RefreshDatabase;

    private function item(Content $content, array $attributes = []): MerchandiseItem
    {
        return MerchandiseItem::create(array_merge([
            'category_id' => $content->category_id,
            'content_id' => $content->id,
            'name' => 'Figure '.MerchandiseItem::count(),
            'tag' => 'standard',
        ], $attributes));
    }

    private function story(string $name = 'Custom fandom'): Content
    {
        $category = Category::create(['name' => $name]);

        return Content::create(['category_id' => $category->id, 'title' => $name.' story', 'status' => 'published']);
    }

    public function test_filters_combine_and_support_database_categories_and_literal_search(): void
    {
        $story = $this->story();
        $match = $this->item($story, ['name' => 'Rare 100% figure', 'is_upcoming' => true, 'tag' => 'collectible']);
        $this->item($story, ['name' => 'Rare 100 percent figure', 'is_upcoming' => true, 'tag' => 'collectible']);
        $this->item($story, ['name' => 'Other 100% figure', 'tag' => 'collectible']);
        $this->item($this->story('Other fandom'), ['name' => 'Foreign 100% figure', 'is_upcoming' => true, 'tag' => 'collectible']);
        $url = '/discover/merchandise?'.http_build_query(['q' => '100%', 'category' => 'custom-fandom', 'status' => 'upcoming', 'tag' => 'collectible']);
        $this->get($url)->assertOk()->assertViewHas('items', fn ($items) => $items->modelKeys() === [$match->id])
            ->assertSee('Custom fandom')->assertSee('Clear filters');
        $this->get('/discover/merchandise?status=released')->assertOk()
            ->assertViewHas('items', fn ($items) => $items->count() === 1 && !$items->first()->is_upcoming);
    }

    public function test_sorting_and_pagination_keep_filters_and_eager_load_cards(): void
    {
        $story = $this->story();
        for ($i = 0; $i < 14; $i++) {
            $item = $this->item($story, ['name' => sprintf('Figure %02d', $i), 'view_count' => $i]);
            $item->forceFill(['created_at' => now()->subDays($i)])->save();
        }
        $this->get('/discover/merchandise')->assertOk()->assertViewHas('items', fn ($items) => $items->first()->name === 'Figure 00');
        $this->get('/discover/merchandise?sort=popular')->assertOk()->assertViewHas('items', fn ($items) => $items->first()->name === 'Figure 13');
        $this->get('/discover/merchandise?q=Figure&category=custom-fandom&status=released&tag=standard&sort=name')->assertOk()
            ->assertSee('Showing 1–12 of 14 items')->assertSee('Go to page 2')
            ->assertViewHas('items', function ($items) {
                parse_str(parse_url($items->nextPageUrl(), PHP_URL_QUERY), $query);

                return $items->total() === 14 && $items->count() === 12
                    && $items->first()->name === 'Figure 00'
                    && $items->every(fn ($item) => $item->relationLoaded('category') && $item->relationLoaded('imageMedia'))
                    && $query === ['q' => 'Figure', 'category' => 'custom-fandom', 'status' => 'released', 'tag' => 'standard', 'sort' => 'name', 'page' => '2'];
            });
        $this->get('/discover/merchandise?q=Figure&category=custom-fandom&status=released&tag=standard&sort=name&page=2')
            ->assertOk()->assertSee('Showing 13–14 of 14 items')->assertSee('Page 2 of 2')
            ->assertSee('aria-current="page" aria-label="Page 2"', false)
            ->assertViewHas('items', fn ($items) => $items->count() === 2 && $items->first()->name === 'Figure 12');
    }

    public function test_empty_states_and_invalid_filters_are_handled(): void
    {
        $this->get('/discover/merchandise')->assertOk()->assertSee('No merchandise is currently available.');
        $this->get('/discover/merchandise?q=missing')->assertOk()->assertSee('No merchandise matches your selected filters.')->assertSee('Browse all merchandise');
        foreach (['q' => ['bad'], 'sort' => 'price', 'status' => 'in-stock', 'tag' => 'sale'] as $key => $value) {
            $this->from('/discover/merchandise')->get('/discover/merchandise?'.http_build_query([$key => $value]))
                ->assertRedirect('/discover/merchandise')->assertSessionHasErrors($key);
        }
        $this->get('/discover/merchandise?category=unknown')->assertNotFound();
    }

    public function test_detail_prioritizes_same_story_excludes_self_and_hides_private_story(): void
    {
        $story = $this->story();
        $item = $this->item($story, ['is_upcoming' => true]);
        $sameStory = $this->item($story);
        $otherStory = Content::create(['category_id' => $story->category_id, 'title' => 'Another story', 'status' => 'published']);
        for ($i = 0; $i < 5; $i++) $this->item($otherStory, ['view_count' => 100]);
        $this->get(route('public.merchandise', $item->slug))->assertOk()
            ->assertSee(route('public.content', $story->slug))->assertSee('More details about this item are on the way.')
            ->assertSee(asset(config('homepage.images.merchandise')))
            ->assertViewHas('related', fn ($items) => $items->count() === 4 && $items->first()->id === $sameStory->id && !$items->contains('id', $item->id));
        $story->update(['status' => 'draft']);
        $this->get(route('public.merchandise', $item->slug))->assertOk()->assertDontSee(route('public.content', $story->slug));
        $story->category->delete();
        $this->get(route('public.merchandise', $item->slug))->assertOk();
    }
}
