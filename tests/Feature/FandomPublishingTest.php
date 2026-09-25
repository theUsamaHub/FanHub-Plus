<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Content;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FandomPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_fandom_renders_with_its_own_identity_and_empty_state(): void
    {
        foreach (config('fandoms') as $slug => $fandom) {
            $this->get(route('public.explore', ['category' => $slug]))->assertOk()
                ->assertSee($fandom['name'].' | Fan Hub Plus')
                ->assertSee(implode(' ', $fandom['lines']))
                ->assertSee('A new chapter is on its way.');
        }
    }

    public function test_admin_story_and_cover_publish_to_fandom_and_disappear_when_unpublished(): void
    {
        $admin = User::factory()->create();
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $admin->roles()->attach($role);
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $cover = Media::create([
            'uploaded_by' => $admin->id, 'disk' => 'public', 'path' => 'uploads/unique-cover.png',
            'original_filename' => 'unique-cover.png', 'media_type' => 'image',
            'mime_type' => 'image/png', 'size_bytes' => 100, 'alt_text' => 'Moonlit adventure cover',
        ]);
        $this->actingAs($admin)->post(route('admin.contents.store'), [
            'title' => 'A brand new adventure', 'category_id' => $category->id,
            'type' => 'article', 'status' => 'published', 'is_featured' => 1,
            'excerpt' => 'The next chapter starts here.', 'body' => "First chapter.\n\nSecond chapter.",
            'cover_media_id' => $cover->id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.contents.index'));
        $story = Content::where('slug', 'a-brand-new-adventure')->firstOrFail();
        auth()->logout();
        $this->get('/explore?category=anime')->assertOk()->assertSee($story->title)
            ->assertSee($cover->url)->assertSee("EDITOR'S PICK", false);
        $this->get('/explore?category=gaming')->assertOk()->assertDontSee($story->title);
        $this->get(route('public.content', $story->slug))->assertOk()
            ->assertSee('First chapter.')->assertSee('Second chapter.')->assertSee($cover->alt_text);
        $this->actingAs($admin)->put(route('admin.contents.update', $story), [
            'title' => $story->title, 'category_id' => $category->id, 'type' => 'article', 'status' => 'draft',
        ])->assertSessionHasNoErrors();
        auth()->logout();
        $this->get('/explore?category=anime')->assertOk()->assertDontSee($story->title);
        $this->get(route('public.content', $story->slug))->assertNotFound();
    }

    public function test_related_stories_exclude_other_fandoms_drafts_and_future_publications(): void
    {
        $anime = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $gaming = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $story = Content::create(['title' => 'Main chapter', 'category_id' => $anime->id, 'status' => 'published']);
        foreach ([
            ['title' => 'Related chapter', 'category_id' => $anime->id, 'status' => 'published'],
            ['title' => 'Private chapter', 'category_id' => $anime->id, 'status' => 'draft'],
            ['title' => 'Scheduled chapter', 'category_id' => $anime->id, 'status' => 'published', 'published_at' => now()->addDay()],
            ['title' => 'Gaming chapter', 'category_id' => $gaming->id, 'status' => 'published'],
        ] as $attributes) {
            Content::create($attributes);
        }
        $this->get(route('public.content', $story->slug))->assertOk()->assertSee('Related chapter')
            ->assertDontSee('Private chapter')->assertDontSee('Scheduled chapter')->assertDontSee('Gaming chapter');
        $this->get('/stories/scheduled-chapter')->assertNotFound();
    }
}
