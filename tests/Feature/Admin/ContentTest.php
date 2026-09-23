<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Media;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);

        $this->category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
    }

    private function media(string $type = 'image', string $ext = 'png'): Media
    {
        return Media::create([
            'uploaded_by' => $this->admin->id,
            'path' => "uploads/{$type}/file.{$ext}",
            'original_filename' => "file.{$ext}",
            'mime_type' => ($type === 'image' ? 'image/png' : ($type === 'video' ? 'video/mp4' : 'application/pdf')),
            'media_type' => $type,
            'size_bytes' => 100,
            'disk' => 'public',
        ]);
    }

    public function test_admin_can_list_and_create_content(): void
    {
        $this->actingAs($this->admin)->get(route('admin.contents.index'))->assertOk();

        $response = $this->actingAs($this->admin)->post(route('admin.contents.store'), [
            'title' => 'Top Anime Openings',
            'category_id' => $this->category->id,
            'type' => 'article',
            'excerpt' => 'Best openings',
            'body' => 'Full body text',
            'status' => 'draft',
            'release_date' => '2026-01-15',
        ]);

        $response->assertRedirect(route('admin.contents.index'));
        $this->assertDatabaseHas('contents', [
            'title' => 'Top Anime Openings',
            'slug' => 'top-anime-openings',
            'category_id' => $this->category->id,
            'status' => 'draft',
            'is_user_submitted' => false,
        ]);
    }

    public function test_content_create_requires_title_and_category(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.contents.store'), ['type' => 'article', 'status' => 'draft'])
            ->assertSessionHasErrors(['title', 'category_id']);
    }

    public function test_content_slug_must_be_unique(): void
    {
        Content::create([
            'category_id' => $this->category->id,
            'title' => 'Existing',
            'slug' => 'existing',
            'type' => 'article',
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.contents.store'), [
                'title' => 'Another',
                'slug' => 'existing',
                'category_id' => $this->category->id,
                'type' => 'article',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_content_and_sync_tags_and_media(): void
    {
        $tag = Tag::create(['name' => 'Opening', 'slug' => 'opening']);
        $cover = $this->media('image');
        $gallery = $this->media('image');
        $trailer = $this->media('video', 'mp4');

        $content = Content::create([
            'category_id' => $this->category->id,
            'title' => 'Old Title',
            'slug' => 'old-title',
            'type' => 'article',
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)->put(route('admin.contents.update', $content), [
            'title' => 'New Title',
            'category_id' => $this->category->id,
            'type' => 'video',
            'status' => 'published',
            'is_featured' => 1,
            'tags' => [$tag->id],
            'cover_media_id' => $cover->id,
            'gallery_media_ids' => [$gallery->id],
            'trailer_media_id' => $trailer->id,
        ])->assertRedirect(route('admin.contents.index'));

        $content->refresh();
        $this->assertSame('New Title', $content->title);
        $this->assertSame('published', $content->status);
        $this->assertTrue($content->is_featured);
        $this->assertNotNull($content->published_at);
        $this->assertTrue($content->tags->contains($tag));
        $this->assertSame(3, $content->media()->count());
        $this->assertSame($cover->id, $content->media()->where('content_media.role', 'cover')->value('media_id'));
    }

    public function test_publishing_sets_published_at_when_empty(): void
    {
        $this->actingAs($this->admin)->post(route('admin.contents.store'), [
            'title' => 'Published Piece',
            'category_id' => $this->category->id,
            'type' => 'article',
            'status' => 'published',
        ]);

        $content = Content::firstWhere('title', 'Published Piece');
        $this->assertNotNull($content->published_at);
    }

    public function test_admin_can_toggle_featured_and_update_status(): void
    {
        $content = Content::create([
            'category_id' => $this->category->id,
            'title' => 'Feature Me',
            'slug' => 'feature-me',
            'type' => 'article',
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.contents.feature', $content))
            ->assertRedirect();

        $this->assertTrue($content->fresh()->is_featured);

        $this->actingAs($this->admin)
            ->patch(route('admin.contents.status', $content), ['status' => 'rejected'])
            ->assertRedirect();

        $content->refresh();
        $this->assertSame('rejected', $content->status);
        $this->assertSame($this->admin->id, $content->reviewed_by);
    }

    public function test_admin_can_filter_contents(): void
    {
        Content::create([
            'category_id' => $this->category->id,
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'type' => 'article',
            'status' => 'draft',
        ]);
        Content::create([
            'category_id' => $this->category->id,
            'title' => 'Published Video',
            'slug' => 'published-video',
            'type' => 'video',
            'status' => 'published',
            'view_count' => 100,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.contents.index', ['status' => 'published', 'type' => 'video']))
            ->assertOk()
            ->assertSee('Published Video')
            ->assertDontSee('Draft Article');

        $this->actingAs($this->admin)
            ->get(route('admin.contents.index', ['sort' => 'views']))
            ->assertOk();
    }

    public function test_admin_can_delete_content(): void
    {
        $content = Content::create([
            'category_id' => $this->category->id,
            'title' => 'Delete Me',
            'slug' => 'delete-me',
            'type' => 'article',
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.contents.destroy', $content))
            ->assertRedirect(route('admin.contents.index'));

        $this->assertDatabaseMissing('contents', ['id' => $content->id]);
    }

    public function test_non_admin_cannot_manage_content(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.contents.index'))->assertRedirect(route('user.dashboard'));
        $this->actingAs($user)->post(route('admin.contents.store'), [
            'title' => 'Nope',
            'category_id' => $this->category->id,
            'type' => 'article',
            'status' => 'draft',
        ])->assertRedirect(route('user.dashboard'));
    }
}
