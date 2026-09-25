<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Media;
use App\Models\User;
use App\Services\HomepageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterSpotlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_spotlight_uses_seven_database_records_and_refreshes_after_edits(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        for ($i = 0; $i < 8; $i++) {
            CharacterProfile::create(['name' => 'Database icon '.$i, 'category_id' => $category->id]);
        }

        $characters = app(HomepageService::class)->sections()['characters'];
        $this->assertCount(7, $characters);
        $this->get('/')->assertOk()->assertSee('Database icon 7')->assertDontSee('Database icon 0')
            ->assertSee('CHARACTER SPOTLIGHT')->assertSee('Stories change. Icons remain.')
            ->assertSee(asset(config('homepage.images.character')))
            ->assertSee(route('public.character', $characters->first()->slug));
        $characters->first()->update(['name' => 'Updated icon']);
        $this->get('/')->assertSee('Updated icon')->assertDontSee('Database icon 7');
        $characters->first()->delete();
        $this->get('/')->assertDontSee('Updated icon')->assertSee('Database icon 1');
    }

    public function test_database_image_takes_priority_and_missing_or_non_image_media_uses_fallback(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $character = CharacterProfile::create(['name' => 'Uploaded icon', 'category_id' => $category->id]);
        $this->assertSame(asset(config('homepage.images.character')), $character->artwork_url);
        $image = Media::create([
            'disk' => 'public', 'path' => 'characters/portrait.jpg', 'original_filename' => 'portrait.jpg',
            'mime_type' => 'image/jpeg', 'media_type' => 'image', 'size_bytes' => 100,
        ]);
        $character->update(['image_media_id' => $image->id]);
        $this->get('/')->assertSee($image->url);
        $image->update(['path' => 'characters/replaced.jpg']);
        $this->get('/')->assertSee($image->fresh()->url);
        $image->update(['media_type' => 'video']);
        $this->assertSame(asset(config('homepage.images.character')), $character->fresh()->artwork_url);
        foreach (['character', 'characters_dark', 'characters_light'] as $key) {
            $this->assertFileExists(public_path(config('homepage.images.'.$key)));
        }
    }

    public function test_detail_is_public_and_excludes_unpublished_related_stories(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $character = CharacterProfile::create(['name' => 'Public icon', 'category_id' => $category->id, 'bio' => '<script>unsafe()</script><p>Database biography.</p>']);
        foreach (['published', 'draft', 'future'] as $state) {
            $story = Content::create([
                'title' => ucfirst($state).' character story', 'type' => 'article', 'category_id' => $category->id,
                'status' => $state === 'draft' ? 'draft' : 'published',
                'published_at' => $state === 'future' ? now()->addDay() : now()->subDay(),
            ]);
            $character->contents()->attach($story);
        }
        $url = route('public.character', $character->slug);
        $this->get($url)->assertOk()->assertSee('Public icon')->assertSee('Database biography.')
            ->assertSee('Published character story')->assertDontSee('Draft character story')
            ->assertDontSee('Future character story')->assertDontSee('<script>unsafe()', false);
        $this->actingAs(User::factory()->create())->get($url)->assertOk()->assertSee('Public icon');
        $this->get('/characters/does-not-exist')->assertNotFound();
    }

    public function test_empty_spotlight_has_no_fake_cards(): void
    {
        $this->get('/')->assertOk()->assertSee('Character spotlights will appear here soon.')
            ->assertDontSee('data-character-carousel', false);
    }
}
