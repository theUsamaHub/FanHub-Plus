<?php

namespace Tests\Feature;

use App\Models\{Category, Content, Media, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicArtworkTest extends TestCase
{
    use RefreshDatabase;

    private function image(string $path): Media
    {
        return Media::create(['disk' => 'public', 'path' => $path, 'original_filename' => basename($path),
            'mime_type' => 'image/jpeg', 'media_type' => 'image', 'size_bytes' => 100]);
    }

    public function test_category_upload_is_used_in_explore_collage_and_both_category_heroes(): void
    {
        $image = $this->image('uploads/category-art.jpg');
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime', 'icon_media_id' => $image->id]);
        Content::create(['title' => 'Featured story', 'category_id' => $category->id, 'status' => 'published', 'is_featured' => true]);
        $this->get(route('public.explore'))->assertOk()->assertSee($image->url);
        foreach ([route('public.explore', ['category' => 'anime']), route('public.fandom', 'anime')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertMatchesRegularExpression('/class="discovery-cover">\s*<img src="'.preg_quote(e($image->url), '/').'"/', $html);
        }
        $image->update(['path' => 'uploads/replaced-category-art.jpg']);
        $this->get(route('public.fandom', 'anime'))->assertSee($image->fresh()->url)->assertDontSee('uploads/category-art.jpg');
    }

    public function test_category_artwork_falls_back_for_missing_or_non_image_uploads(): void
    {
        $category = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        $fallback = asset(config('homepage.artwork.gaming'));
        $this->assertSame($fallback, $category->artwork_url);
        $media = $this->image('uploads/not-an-image.mp4');
        $media->update(['media_type' => 'video']);
        $category->update(['icon_media_id' => $media->id]);
        $this->assertSame($fallback, $category->fresh()->artwork_url);
        $media->update(['media_type' => 'image', 'path' => '']);
        $this->assertSame($fallback, $category->fresh()->artwork_url);
    }

    public function test_uploaded_avatar_appears_in_navbar_and_clean_dashboard_and_can_be_removed(): void
    {
        $user = User::factory()->create(['name' => 'Avatar member']);
        $role = Role::firstOrCreate(['slug' => 'registered-user'], ['name' => 'Registered User']);
        $user->roles()->syncWithoutDetaching([$role->id]);
        $avatar = $this->image('uploads/avatars/member.jpg');
        $user->profile()->updateOrCreate([], ['avatar_media_id' => $avatar->id]);
        $this->actingAs($user->fresh())->get(route('public.explore'))->assertOk()->assertSee($avatar->url)->assertSee('data-avatar-image', false);
        $html = $this->get(route('user.dashboard'))->assertOk()->assertSee('member-welcome--clean', false)
            ->assertDontSee('member-welcome__art', false)->getContent();
        $this->assertSame(2, substr_count($html, 'src="'.e($avatar->url).'"'));
        $user->profile()->update(['avatar_media_id' => null]);
        $this->actingAs($user->fresh())->get(route('user.dashboard'))->assertOk()->assertDontSee($avatar->url)
            ->assertDontSee('data-avatar-image', false)->assertSee('user-avatar', false);
    }
}
