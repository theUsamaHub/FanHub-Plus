<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);
    }

    private function media(array $attrs = []): Media
    {
        return Media::create(array_merge([
            'uploaded_by' => $this->admin->id,
            'path' => 'uploads/images/test.png',
            'original_filename' => 'test.png',
            'mime_type' => 'image/png',
            'media_type' => 'image',
            'size_bytes' => 1024,
            'disk' => 'public',
        ], $attrs));
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Anime',
            'description' => 'Anime content',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Anime', 'slug' => 'anime']);
    }

    public function test_category_create_requires_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_category_slug_must_be_unique(): void
    {
        Category::create(['name' => 'Anime', 'slug' => 'anime']);

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Anime 2',
                'slug' => 'anime',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_category_soft_delete_and_force_delete_blocked_when_referenced(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        Content::create([
            'category_id' => $category->id,
            'title' => 'Top Openings',
            'slug' => 'top-openings',
            'type' => 'article',
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted('categories', ['id' => $category->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.force-delete', $category->id))
            ->assertRedirect(route('admin.categories.trashed'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_upload_media(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('cover.png', 10, 'image/png');

        $this->actingAs($this->admin)->post(route('admin.media.store'), [
            'files' => [$file],
            'alt_text' => 'Cover image',
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'original_filename' => 'cover.png',
            'media_type' => 'image',
            'alt_text' => 'Cover image',
        ]);
    }

    public function test_media_upload_rejects_disallowed_extension(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream');

        $this->actingAs($this->admin)
            ->post(route('admin.media.store'), ['files' => [$file]])
            ->assertSessionHasErrors('files');
    }

    public function test_media_update_alt_text_and_duration(): void
    {
        $media = $this->media(['media_type' => 'video', 'mime_type' => 'video/mp4', 'original_filename' => 'clip.mp4']);

        $this->actingAs($this->admin)
            ->put(route('admin.media.update', $media), [
                'alt_text' => 'Trailer clip',
                'duration_hours' => 1,
                'duration_minutes' => 2,
                'duration_seconds' => 5.5,
            ])
            ->assertRedirect(route('admin.media.index'));

        // 1h 2m 5.5s = 3725.5 seconds
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'alt_text' => 'Trailer clip',
            'duration' => 3725.5,
        ]);
    }

    public function test_media_duration_parts_convert_to_seconds(): void
    {
        $media = $this->media(['media_type' => 'audio', 'mime_type' => 'audio/mpeg', 'original_filename' => 'song.mp3']);

        $this->actingAs($this->admin)
            ->put(route('admin.media.update', $media), [
                'duration_hours' => 0,
                'duration_minutes' => 3,
                'duration_seconds' => 30,
            ])
            ->assertRedirect(route('admin.media.index'));

        $this->assertSame(210.0, (float) $media->fresh()->duration);
        $this->assertSame('0:03:30', $media->fresh()->duration_formatted);
    }

    public function test_media_delete_blocked_when_referenced(): void
    {
        $media = $this->media();
        Category::create([
            'name' => 'Anime',
            'slug' => 'anime',
            'icon_media_id' => $media->id,
        ]);

        $this->assertTrue($media->isReferenced());

        $this->actingAs($this->admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_media_delete_allowed_when_unreferenced(): void
    {
        Storage::fake('public');
        $media = $this->media(['path' => 'uploads/images/orphan.png']);

        $this->actingAs($this->admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_media_upload_with_category_is_saved(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $file = UploadedFile::fake()->create('poster.png', 10, 'image/png');

        $this->actingAs($this->admin)->post(route('admin.media.store'), [
            'files' => [$file],
            'category_id' => $category->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'original_filename' => 'poster.png',
            'category_id' => $category->id,
        ]);
    }

    public function test_media_upload_without_category_stays_general(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('shared.png', 10, 'image/png');

        $this->actingAs($this->admin)->post(route('admin.media.store'), [
            'files' => [$file],
            'category_id' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'original_filename' => 'shared.png',
            'category_id' => null,
        ]);
    }

    public function test_media_upload_rejects_unknown_category(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('poster.png', 10, 'image/png');

        $this->actingAs($this->admin)->post(route('admin.media.store'), [
            'files' => [$file],
            'category_id' => 999999,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_media_update_can_assign_and_clear_category(): void
    {
        $category = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $media = $this->media();

        $this->actingAs($this->admin)
            ->put(route('admin.media.update', $media), ['category_id' => $category->id])
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseHas('media', ['id' => $media->id, 'category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.media.update', $media), ['category_id' => ''])
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseHas('media', ['id' => $media->id, 'category_id' => null]);
    }

    public function test_media_index_filters_by_category_and_general(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $categorized = $this->media(['original_filename' => 'anime-cover.png', 'category_id' => $category->id]);
        $general = $this->media(['original_filename' => 'shared-cover.png']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.index', ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('anime-cover.png')
            ->assertDontSee('shared-cover.png');

        $this->actingAs($this->admin)
            ->get(route('admin.media.index', ['category_id' => 'general']))
            ->assertOk()
            ->assertSee('shared-cover.png')
            ->assertDontSee('anime-cover.png');

        $this->actingAs($this->admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('anime-cover.png')
            ->assertSee('shared-cover.png');
    }

    public function test_media_edit_form_shows_category_selector(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $media = $this->media(['category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.media.edit', $media))
            ->assertOk()
            ->assertSee('name="category_id"', false)
            ->assertSee($category->name);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.categories.index'))->assertRedirect(route('user.dashboard'));
        $this->actingAs($user)->get(route('admin.media.index'))->assertRedirect(route('user.dashboard'));
        $this->actingAs($user)->post(route('admin.categories.store'), ['name' => 'X'])->assertRedirect(route('user.dashboard'));
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
        $this->get(route('admin.media.index'))->assertRedirect(route('login'));
    }
}
