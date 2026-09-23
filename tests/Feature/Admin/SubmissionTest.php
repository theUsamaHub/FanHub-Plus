<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $submitter;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);

        $this->submitter = User::factory()->create();
        $this->category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
    }

    private function submission(array $attrs = []): Content
    {
        return Content::create(array_merge([
            'category_id' => $this->category->id,
            'title' => 'Fan Submission',
            'slug' => 'fan-submission',
            'type' => 'article',
            'status' => 'pending_review',
            'is_user_submitted' => true,
            'submitted_by' => $this->submitter->id,
        ], $attrs));
    }

    public function test_admin_sees_pending_submissions_queue(): void
    {
        $this->submission();
        Content::create([
            'category_id' => $this->category->id,
            'title' => 'Admin Content',
            'slug' => 'admin-content',
            'type' => 'article',
            'status' => 'draft',
            'is_user_submitted' => false,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.index'))
            ->assertOk()
            ->assertSee('Fan Submission')
            ->assertDontSee('Admin Content');
    }

    public function test_admin_can_preview_submission(): void
    {
        $submission = $this->submission(['body' => 'Submission body text']);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Fan Submission')
            ->assertSee('Submission body text')
            ->assertDontSee('Edit');
    }

    public function test_submission_review_does_not_offer_user_role_editing(): void
    {
        $submission = $this->submission();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.submissions.show', $submission))
            ->assertOk();

        $response->assertDontSee('Edit User');
        $response->assertDontSee(route('admin.users.edit', $this->submitter));
        $response->assertDontSee(route('admin.contents.edit', $submission));
    }

    public function test_approve_publishes_submission_and_sets_reviewer(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.approve', $submission))
            ->assertRedirect(route('admin.submissions.index'));

        $submission->refresh();
        $this->assertSame('published', $submission->status);
        $this->assertSame($this->admin->id, $submission->reviewed_by);
        $this->assertNotNull($submission->published_at);
    }

    public function test_reject_marks_submission_rejected_and_sets_reviewer(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.reject', $submission))
            ->assertRedirect(route('admin.submissions.index'));

        $submission->refresh();
        $this->assertSame('rejected', $submission->status);
        $this->assertSame($this->admin->id, $submission->reviewed_by);
    }

    public function test_cannot_moderate_admin_content_through_submissions(): void
    {
        $content = Content::create([
            'category_id' => $this->category->id,
            'title' => 'Admin Content',
            'slug' => 'admin-content',
            'type' => 'article',
            'status' => 'draft',
            'is_user_submitted' => false,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.show', $content))
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.approve', $content))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_moderate_submissions(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->submitter)
            ->patch(route('admin.submissions.approve', $submission))
            ->assertRedirect(route('user.dashboard'));

        $this->assertSame('pending_review', $submission->fresh()->status);
    }
}
