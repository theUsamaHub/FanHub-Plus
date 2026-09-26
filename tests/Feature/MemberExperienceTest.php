<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, Category, CharacterProfile, Content, Media, Role, Tag, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberExperienceTest extends TestCase
{
    use RefreshDatabase;

    private User $member;
    private Category $category;
    private Content $story;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create(['name' => 'Hassan']);
        $role = Role::firstOrCreate(['slug' => 'registered-user'], ['name' => 'Registered User']);
        $this->member->roles()->syncWithoutDetaching([$role->id]);
        $this->category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $this->story = Content::create(['category_id' => $this->category->id, 'title' => 'A public story', 'type' => 'article', 'status' => 'published', 'body' => 'A thoughtful article about our favorite worlds.']);
    }

    public function test_all_member_pages_render_and_old_links_reach_real_pages(): void
    {
        $this->get('/user/dashboard')->assertRedirect(route('login'));
        $this->actingAs($this->member);
        foreach (['dashboard', 'bookmarks', 'favorites', 'activity', 'reviews', 'submissions', 'submissions/create', 'feedback'] as $page) {
            $this->get('/user/'.$page)->assertOk()->assertDontSee('coming soon', false);
        }
        $this->get('/profile')->assertOk()->assertSee('Display preferences');
        $this->get('/dashboard')->assertRedirect(route('user.dashboard'));
        $this->get('/account/bookmarks')->assertRedirect(route('user.bookmarks'));
        $this->get('/account/submit-content')->assertRedirect(route('user.submissions.create'));
    }

    public function test_bookmarks_are_idempotent_notes_are_private_and_unpublished_targets_are_blocked(): void
    {
        $this->actingAs($this->member);
        $url = route('user.bookmark', ['content', $this->story->id]);
        $this->post($url, ['saved' => true])->assertRedirect();
        $this->post($url, ['saved' => true])->assertRedirect();
        $this->assertDatabaseCount('bookmarks', 1);
        $bookmark = $this->member->bookmarks()->first();
        $this->patch(route('user.bookmarks.note', $bookmark), ['note' => 'A private thought'])->assertSessionHasNoErrors();
        $this->get('/user/bookmarks')->assertSee('A private thought');
        $this->actingAs(User::factory()->create())->get('/user/bookmarks')->assertDontSee('A private thought');
        $this->patch(route('user.bookmarks.note', $bookmark), ['note' => 'Stolen'])->assertForbidden();
        $this->delete(route('user.bookmarks.destroy', $bookmark))->assertForbidden();
        $this->story->update(['status' => 'draft']);
        $this->actingAs($this->member)->post($url, ['saved' => true])->assertNotFound();
        $this->get('/user/bookmarks')->assertDontSee('A public story')->assertSee('Item unavailable')->assertSee('A private thought');
        $this->post(route('user.bookmark', ['user', $this->member->id]), ['saved' => true])->assertNotFound();
    }

    public function test_favorites_and_reading_history_personalize_dashboard_without_other_users_activity(): void
    {
        $other = User::factory()->create();
        ActivityLog::create(['user_id' => $other->id, 'event' => 'member.viewed', 'auditable_type' => Content::class, 'auditable_id' => $this->story->id, 'new_values' => ['label' => 'Other private history']]);
        $this->actingAs($this->member)->post(route('user.favorites.store', $this->category), ['saved' => true])->assertRedirect();
        $this->get(route('public.content', $this->story->slug))->assertOk();
        $this->get(route('public.content', $this->story->slug))->assertOk();
        $this->assertEquals(1, ActivityLog::where('user_id', $this->member->id)->where('event', 'member.viewed')->count());
        $this->get('/user/dashboard')->assertOk()->assertSee('Hassan')->assertSee('A public story')->assertSee('Anime')->assertDontSee('Other private history');
        $this->post(route('user.favorites.store', $this->category), ['saved' => false]);
        $this->assertCount(0, $this->member->fresh()->favoriteCategories);
    }

    public function test_rating_updates_one_record_and_reviews_require_moderation(): void
    {
        $this->actingAs($this->member);
        $url = route('user.rating', ['content', $this->story->id]);
        $this->post($url, ['stars' => 8])->assertSessionHasErrors('stars');
        $this->post($url, ['stars' => 5])->assertSessionHasNoErrors();
        $this->post($url, ['stars' => 3])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('ratings', 1);
        $this->assertDatabaseHas('ratings', ['user_id' => $this->member->id, 'stars' => 3]);
        $this->post(route('user.review', ['content', $this->story->id]), ['body' => 'This review needs moderation.', 'status' => 'approved']);
        $review = $this->member->reviews()->first();
        $this->assertEquals('pending', $review->status);
        $this->actingAs(User::factory()->create())->get(route('public.content', $this->story->slug))->assertDontSee('This review needs moderation.');
        $this->delete(route('user.reviews.destroy', $review))->assertForbidden();
        $review->update(['status' => 'approved']);
        $this->get(route('public.content', $this->story->slug))->assertSee('This review needs moderation.');
    }

    public function test_submissions_cannot_self_publish_or_edit_other_users_work(): void
    {
        $this->actingAs($this->member);
        $payload = ['title' => 'My fan article', 'type' => 'article', 'category_id' => $this->category->id,
            'body' => 'A long and thoughtful fan contribution for the community.', 'intent' => 'submit',
            'status' => 'published', 'is_featured' => 1, 'submitted_by' => 999];
        $this->post(route('user.submissions.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('user.submissions'));
        $submission = $this->member->submittedContents()->first();
        $this->assertEquals('pending_review', $submission->status);
        $this->assertFalse($submission->is_featured);
        $this->get(route('public.content', $submission->slug))->assertNotFound();
        $this->get(route('user.submissions.edit', $submission))->assertOk();
        $this->actingAs(User::factory()->create())->put(route('user.submissions.update', $submission), $payload)->assertForbidden();
        $this->delete(route('user.submissions.destroy', $submission))->assertForbidden();
        $submission->update(['status' => 'rejected']);
        $this->actingAs($this->member)->put(route('user.submissions.update', $submission), $payload)->assertSessionHasNoErrors();
        $this->assertEquals('pending_review', $submission->fresh()->status);
        $submission->update(['status' => 'published']);
        $this->put(route('user.submissions.update', $submission), $payload)->assertForbidden();
    }

    public function test_media_submission_uploads_and_rejects_missing_or_wrong_attachment(): void
    {
        Storage::fake('public');
        $payload = ['title' => 'Fan portrait', 'type' => 'image', 'category_id' => $this->category->id, 'body' => str_repeat('A portrait of a favorite character. ', 2), 'intent' => 'submit'];
        $this->actingAs($this->member)->post(route('user.submissions.store'), $payload)->assertSessionHasErrors('attachment');
        $image = UploadedFile::fake()->createWithContent('portrait.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aG1cAAAAASUVORK5CYII='));
        $this->post(route('user.submissions.store'), [...$payload, 'attachment' => $image])->assertSessionHasNoErrors();
        $submission = $this->member->submittedContents()->first();
        $this->assertEquals('pending_review', $submission->status);
        $this->assertEquals('gallery', $submission->media->first()->pivot->role);
        Storage::disk('public')->assertExists($submission->media->first()->path);
    }

    public function test_profile_preferences_and_avatar_ownership_are_validated(): void
    {
        $foreign = Media::create(['uploaded_by' => User::factory()->create()->id, 'disk' => 'public', 'path' => 'private.jpg', 'original_filename' => 'private.jpg', 'mime_type' => 'image/jpeg', 'media_type' => 'image', 'size_bytes' => 12]);
        $this->actingAs($this->member)->patch('/profile', ['name' => 'Hassan', 'avatar_media_id' => $foreign->id])->assertSessionHasErrors('avatar_media_id');
        $this->patch('/profile', ['name' => 'Hassan', 'display_name' => 'Fan Hassan', 'theme_preference' => 'light', 'font_size_preference' => 'large', 'favorites_present' => 1, 'favorites' => [$this->category->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('user_profiles', ['user_id' => $this->member->id, 'theme_preference' => 'light', 'font_size_preference' => 'large']);
        $this->assertCount(1, $this->member->fresh()->favoriteCategories);
        $this->patch('/profile', ['name' => 'Hassan', 'favorites_present' => 1])->assertSessionHasNoErrors();
        $this->assertCount(0, $this->member->fresh()->favoriteCategories);
    }

    public function test_feedback_is_categorized_and_private_and_watched_history_is_real(): void
    {
        $this->actingAs($this->member)->post('/user/feedback', ['type' => 'suggestion', 'message' => 'Please add a new community category.', 'status' => 'resolved'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('feedback', ['user_id' => $this->member->id, 'status' => 'open']);
        $this->actingAs(User::factory()->create())->get('/user/feedback')->assertDontSee('Please add a new community category.');
        $this->story->update(['type' => 'video']);
        $this->actingAs($this->member)->post(route('user.watched', $this->story), ['watched' => true])->assertRedirect();
        $this->post(route('user.watched', $this->story), ['watched' => true]);
        $this->assertEquals(1, ActivityLog::where('event', 'member.watched')->count());
        $this->post(route('user.watched', $this->story), ['watched' => false]);
        $this->assertEquals(0, ActivityLog::where('event', 'member.watched')->count());
    }

    public function test_discovery_filters_and_rich_articles_do_not_expose_unsafe_html(): void
    {
        $tag = Tag::create(['name' => 'Adventure']);
        $this->story->update(['release_date' => '2025-01-01', 'body' => '<h2>Real heading</h2><p>Good <strong>story</strong></p><script>alert(1)</script><img src="javascript:alert(1)" onerror="alert(2)"><a href="javascript:alert(1)">Bad link</a>']);
        $this->story->tags()->attach($tag);
        $this->get('/explore?tag='.$tag->id.'&year=2025&sort=alphabetical')->assertOk()->assertSee('A public story');
        $this->get('/explore?year=2024')->assertOk()->assertDontSee('A public story');
        CharacterProfile::create(['category_id' => $this->category->id, 'name' => 'A hero']);
        $this->get('/discover/characters?q=hero')->assertOk()->assertSee('A hero');
        $this->story->update(['type' => 'video']);
        $this->get('/discover/multimedia?type=video')->assertOk()->assertSee('A public story');
        $this->get('/discover/multimedia?type=audio')->assertOk()->assertDontSee('A public story');
        $this->get(route('public.content', $this->story->slug))->assertOk()->assertSee('<h2>Real heading</h2>', false)->assertDontSee('javascript:alert', false)->assertDontSee('onerror=', false)->assertDontSee('<script>alert', false);
    }

    public function test_nearby_events_filter_distance_publication_and_dates(): void
    {
        $base = ['category_id' => $this->category->id, 'city' => 'Karachi', 'start_at' => now()->addWeek(), 'status' => 'published', 'latitude' => 24.86, 'longitude' => 67.01];
        \App\Models\Event::create([...$base, 'title' => 'Nearby fan gathering']);
        \App\Models\Event::create([...$base, 'title' => 'Distant gathering', 'latitude' => 34.05, 'longitude' => -118.24]);
        \App\Models\Event::create([...$base, 'title' => 'Private gathering', 'status' => 'draft']);
        \App\Models\Event::create([...$base, 'title' => 'Old gathering', 'start_at' => now()->subWeek()]);
        $this->get('/events/nearby')->assertOk()->assertSee('Use my location');
        $this->get('/events/nearby?latitude=24.86&longitude=67.01&radius=50')->assertOk()
            ->assertSee('Nearby fan gathering')->assertDontSee('Distant gathering')->assertDontSee('Private gathering')->assertDontSee('Old gathering');
        $this->get('/events/nearby?latitude=100&longitude=200')->assertSessionHasErrors(['latitude', 'longitude']);
    }
}
