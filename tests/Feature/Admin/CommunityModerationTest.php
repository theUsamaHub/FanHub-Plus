<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Feedback;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;
    private Content $content;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);

        $this->member = User::factory()->create();

        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $this->content = Content::create([
            'category_id' => $category->id,
            'title' => 'Top Openings',
            'slug' => 'top-openings',
            'type' => 'article',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_moderate_reviews(): void
    {
        $review = Review::create([
            'user_id' => $this->member->id,
            'reviewable_type' => Content::class,
            'reviewable_id' => $this->content->id,
            'title' => 'Great piece',
            'body' => 'Loved it.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Content: "Top Openings"');

        $this->actingAs($this->admin)
            ->patch(route('admin.reviews.approve', $review))
            ->assertRedirect();

        $this->assertSame('approved', $review->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('admin.reviews.reject', $review))
            ->assertRedirect();

        $this->assertSame('rejected', $review->fresh()->status);
    }

    public function test_review_shows_unavailable_for_missing_target(): void
    {
        Review::create([
            'user_id' => $this->member->id,
            'reviewable_type' => Content::class,
            'reviewable_id' => 999999,
            'body' => 'Orphan review',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Unavailable resource');
    }

    public function test_admin_can_view_and_delete_ratings(): void
    {
        Rating::create([
            'user_id' => $this->member->id,
            'rateable_type' => Content::class,
            'rateable_id' => $this->content->id,
            'rating_type' => 'star',
            'stars' => 5,
        ]);

        $thumbs = Rating::create([
            'user_id' => User::factory()->create()->id,
            'rateable_type' => Content::class,
            'rateable_id' => $this->content->id,
            'rating_type' => 'thumbs',
            'is_thumbs_up' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.ratings.index'))
            ->assertOk()
            ->assertSee('Top Openings')
            ->assertSee('5 / 5');

        $this->actingAs($this->admin)
            ->delete(route('admin.ratings.destroy', $thumbs))
            ->assertRedirect(route('admin.ratings.index'));

        $this->assertDatabaseMissing('ratings', ['id' => $thumbs->id]);
    }

    public function test_feedback_status_workflow(): void
    {
        $feedback = Feedback::create([
            'user_id' => $this->member->id,
            'type' => 'bug',
            'message' => 'Broken page on mobile.',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.feedback.index', ['type' => 'bug', 'source' => 'user']))
            ->assertOk()
            ->assertSee('Broken page on mobile.');

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'in_review'])
            ->assertRedirect();

        $this->assertSame('in_review', $feedback->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'resolved'])
            ->assertRedirect();

        $this->assertSame('resolved', $feedback->fresh()->status);
    }

    public function test_feedback_rejects_invalid_status(): void
    {
        $feedback = Feedback::create([
            'user_id' => null,
            'type' => 'query',
            'message' => 'Guest question.',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'archived'])
            ->assertSessionHasErrors('status');
    }

    public function test_non_admin_cannot_moderate_community(): void
    {
        $this->actingAs($this->member)->get(route('admin.reviews.index'))->assertRedirect(route('user.dashboard'));
        $this->actingAs($this->member)->get(route('admin.feedback.index'))->assertRedirect(route('user.dashboard'));
    }
}
