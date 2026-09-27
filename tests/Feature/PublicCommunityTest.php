<?php

namespace Tests\Feature;

use App\Models\{Category, CharacterProfile, Content, Event, MerchandiseItem, Rating, Review, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_are_visible_to_the_author_then_public_after_approval_on_every_supported_page(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create(['category_id' => $category->id, 'title' => 'Upcoming anime', 'type' => 'article',
            'status' => 'published', 'release_date' => now()->addMonth()]);
        $character = CharacterProfile::create(['category_id' => $category->id, 'name' => 'Hero']);
        $event = Event::create(['category_id' => $category->id, 'title' => 'Fan meet', 'city' => 'Karachi', 'status' => 'published', 'start_at' => now()->addDay()]);
        $merchandise = MerchandiseItem::create(['category_id' => $category->id, 'content_id' => $content->id, 'name' => 'Collectible']);
        $author = User::factory()->create();
        $visitor = User::factory()->create();

        foreach ([['content', $content, 'public.content'], ['character', $character, 'public.character'],
            ['event', $event, 'events.show'], ['merchandise', $merchandise, 'public.merchandise'], ['fandom', $category, 'public.fandom']] as [$type, $item, $route]) {
            $url = route($route, $item->slug);
            $body = 'My personal perspective on this '.$type.'.';
            $this->actingAs($author)->from($url)->post(route('user.review', [$type, $item->id]), ['body' => $body])
                ->assertRedirect($url.'#community')->assertSessionHasNoErrors();
            $review = Review::where('reviewable_type', $item->getMorphClass())->where('reviewable_id', $item->id)->firstOrFail();
            $this->assertSame('pending', $review->status);
            $this->get($url)->assertOk()->assertSee('Awaiting approval')->assertSee($body)->assertSee('0 reviews');
            $this->actingAs($visitor)->get($url)->assertOk()->assertDontSee($body);
            $review->update(['status' => 'approved']);
            $this->get($url)->assertOk()->assertSee($body)->assertSee('1 review');
            $this->actingAs($author)->from($url)->post(route('user.review', [$type, $item->id]), ['body' => $body.' Edited.'])->assertSessionHasNoErrors();
            $this->assertSame('pending', $review->fresh()->status);
            $this->actingAs($visitor)->get($url)->assertDontSee($body.' Edited.');
            $review->update(['status' => 'rejected']);
            $this->actingAs($author)->get($url)->assertSee('Not published');
        }
    }

    public function test_star_summary_excludes_thumbs_and_review_pagination_preserves_filters(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create(['title' => 'Star story', 'category_id' => $category->id, 'type' => 'article', 'status' => 'published']);
        foreach (range(1, 6) as $i) {
            $user = User::factory()->create();
            Review::create(['user_id' => $user->id, 'reviewable_type' => Content::class, 'reviewable_id' => $content->id,
                'body' => 'Published perspective number '.$i, 'status' => 'approved']);
            if ($i <= 3) Rating::create(['user_id' => $user->id, 'rateable_type' => Content::class, 'rateable_id' => $content->id,
                'rating_type' => $i === 3 ? 'thumbs' : 'star', 'stars' => $i === 3 ? null : $i + 3, 'is_thumbs_up' => $i === 3 ? true : null]);
        }
        $url = route('public.content', $content->slug);
        $this->get($url.'?page=2')->assertOk()->assertSee('4.5')->assertSee('2 star ratings')->assertSee('6 reviews')
            ->assertSee('5/5')->assertSee('reviews_page=2')->assertSee('page=2')
            ->assertDontSee('Published perspective number 1');
        $this->get($url.'?reviews_page=2')->assertOk()->assertSee('Published perspective number 1');
        $author = User::factory()->create();
        $this->actingAs($author)->post(route('user.rating', ['content', $content->id]), ['stars' => 5])->assertSessionHasNoErrors();
        $this->get($url)->assertSee('3 star ratings')->assertSee('Update rating');
        $this->post(route('user.rating', ['content', $content->id]), ['stars' => 1])->assertSessionHasNoErrors();
        $this->get($url)->assertSee('3 star ratings')->assertSee('3.3');
    }
}
