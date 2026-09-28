<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, Category, CharacterProfile, Content, Event, Media, MerchandiseItem, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_only_shows_linked_characters_merchandise_media_and_public_events(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create(['title' => 'Aether adventure', 'category_id' => $category->id, 'status' => 'published']);
        $other = Content::create(['title' => 'Other adventure', 'category_id' => $category->id, 'status' => 'published']);
        $hero = CharacterProfile::create(['name' => 'Linked hero', 'category_id' => $category->id]);
        $stranger = CharacterProfile::create(['name' => 'Unrelated hero', 'category_id' => $category->id]);
        $content->characters()->attach($hero);
        $other->characters()->attach($stranger);
        MerchandiseItem::create(['name' => 'Linked collectible', 'category_id' => $category->id, 'content_id' => $content->id]);
        MerchandiseItem::create(['name' => 'Unrelated collectible', 'category_id' => $category->id, 'content_id' => $other->id]);
        $uploader = User::factory()->create();
        foreach (['gallery' => ['image', 'image/png'], 'trailer' => ['video', 'video/mp4'], 'audio_clip' => ['audio', 'audio/mpeg'], 'attachment' => ['document', 'application/pdf']] as $role => [$type, $mime]) {
            foreach ([$content, $other] as $owner) {
                $media = Media::create(['uploaded_by' => $uploader->id, 'disk' => 'public', 'path' => "uploads/{$owner->id}-{$role}", 'original_filename' => "$role-file", 'media_type' => $type, 'mime_type' => $mime, 'size_bytes' => 100, 'alt_text' => "{$owner->id} $role asset"]);
                $owner->media()->attach($media, ['role' => $role]);
            }
        }
        foreach ([['Linked public event', 'published', $content], ['Linked draft event', 'draft', $content], ['Unrelated event', 'published', $other]] as [$title, $status, $owner]) {
            Event::create(['title' => $title, 'status' => $status, 'category_id' => $category->id, 'content_id' => $owner->id, 'city' => 'Karachi', 'start_at' => now()->addWeek()]);
        }
        $response = $this->get(route('public.content', $content->slug))->assertOk()
            ->assertSee('Linked hero')->assertSee('Linked collectible')->assertSee('Linked public event')
            ->assertDontSee('Unrelated hero')->assertDontSee('Unrelated collectible')->assertDontSee('Linked draft event')->assertDontSee('Unrelated event')
            ->assertSee('data-character-coverflow', false)->assertSee('data-gallery-dialog', false)
            ->assertDontSee('From the community')->assertDontSee('Your rating')->assertDontSee('Write a review');
        foreach (['gallery', 'trailer', 'audio_clip', 'attachment'] as $role) {
            $response->assertSee("uploads/{$content->id}-{$role}", false)->assertDontSee("uploads/{$other->id}-{$role}", false);
        }
        $this->get(route('public.section', ['section' => 'merchandise', 'content' => $content->id]))
            ->assertOk()->assertSee('Linked collectible')->assertDontSee('Unrelated collectible');
        $content->update(['status' => 'draft']);
        $this->get(route('public.section', ['section' => 'merchandise', 'content' => $content->id]))->assertNotFound();
    }

    public function test_watchlist_is_persistent_idempotent_private_and_independent_of_bookmarks(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create(['title' => 'Watch me', 'category_id' => $category->id, 'status' => 'published']);
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $url = route('user.watchlist', $content);
        $this->post($url, ['saved' => 1])->assertRedirect(route('login'));
        $this->actingAs($user)->post($url, ['saved' => 1])->assertSessionHasNoErrors();
        $this->post($url, ['saved' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, ActivityLog::where('event', 'member.watchlisted')->count());
        $this->get(route('public.content', $content->slug))->assertOk()->assertDontSee('In Watchlist')->assertDontSee('Add to Watchlist')->assertSee('Add to Favorites');
        $this->post(route('user.bookmark', ['content', $content->id]), ['saved' => 1])->assertSessionHasNoErrors();
        $this->get(route('public.content', $content->slug))->assertOk()->assertDontSee('In Watchlist')->assertDontSee('Add to Watchlist')->assertSee('Saved to Favorites');
        $this->actingAs($otherUser)->get(route('public.content', $content->slug))->assertDontSee('In Watchlist')->assertDontSee('Saved to Favorites');
        $this->post($url, ['saved' => 0])->assertSessionHasNoErrors();
        $this->assertSame(1, ActivityLog::where('event', 'member.watchlisted')->count());
        $this->actingAs($user)->post($url, ['saved' => 0])->assertSessionHasNoErrors();
        $this->assertSame(0, ActivityLog::where('event', 'member.watchlisted')->count());
        $this->assertSame(1, $user->bookmarks()->count());
        $content->update(['status' => 'draft']);
        $this->post($url, ['saved' => 1])->assertNotFound();
    }

    public function test_empty_detail_has_no_fake_media_and_scheduled_content_is_not_public(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create(['title' => 'No media yet', 'category_id' => $category->id, 'status' => 'published']);
        $this->get(route('public.content', $content->slug))->assertOk()->assertSee('Trailer coming soon')
            ->assertDontSee('data-gallery-image', false)->assertDontSee('data-audio-player', false);
        $content->update(['published_at' => now()->addDay()]);
        $this->get(route('public.content', $content->slug))->assertNotFound();
        $this->actingAs(User::factory()->create())->post(route('user.watchlist', $content), ['saved' => 1])->assertNotFound();
    }
}
