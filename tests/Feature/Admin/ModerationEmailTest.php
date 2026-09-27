<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Feedback;
use App\Models\Role;
use App\Models\User;
use App\Notifications\FeedbackResolvedNotification;
use App\Notifications\SubmissionApprovedNotification;
use App\Notifications\SubmissionRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ModerationEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $author;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);

        $this->author = User::factory()->create();
        $this->category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
    }

    private function submission(array $overrides = []): Content
    {
        return Content::create(array_merge([
            'category_id' => $this->category->id,
            'title' => 'Fan Submission',
            'slug' => 'fan-submission',
            'type' => 'article',
            'status' => 'pending_review',
            'is_user_submitted' => true,
            'submitted_by' => $this->author->id,
        ], $overrides));
    }

    private function feedback(array $overrides = []): Feedback
    {
        return Feedback::create(array_merge([
            'user_id' => $this->author->id,
            'type' => 'bug',
            'message' => 'The bookmark button does nothing.',
            'status' => 'open',
        ], $overrides));
    }

    public function test_approving_submission_emails_the_submitter(): void
    {
        Notification::fake();
        $content = $this->submission();

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.approve', $content))
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertSame('published', $content->fresh()->status);

        Notification::assertSentTo(
            $this->author,
            SubmissionApprovedNotification::class,
            function (SubmissionApprovedNotification $notification): bool {
                $mail = $notification->toMail($this->author);

                return str_contains($mail->subject, 'published')
                    && str_contains((string) $mail->greeting, $this->author->name)
                    && str_contains((string) $mail->actionText, 'View Published Content');
            }
        );
    }

    public function test_rejecting_submission_emails_the_submitter(): void
    {
        Notification::fake();
        $content = $this->submission();

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.reject', $content))
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertSame('rejected', $content->fresh()->status);

        Notification::assertSentTo(
            $this->author,
            SubmissionRejectedNotification::class,
            function (SubmissionRejectedNotification $notification): bool {
                $mail = $notification->toMail($this->author);

                return str_contains($mail->subject, 'not approved')
                    && str_contains((string) $mail->actionText, 'View My Submissions')
                    && $mail->actionUrl === route('user.submissions');
            }
        );
    }

    public function test_submission_decision_sends_no_email_without_a_submitter(): void
    {
        Notification::fake();
        $content = $this->submission(['submitted_by' => null]);

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.approve', $content));

        Notification::assertNothingSent();
    }

    public function test_repeat_decision_on_same_status_does_not_email_again(): void
    {
        Notification::fake();
        $content = $this->submission(['status' => 'published']);

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.approve', $content));

        Notification::assertNothingSent();
    }

    public function test_resolving_feedback_emails_the_reporter(): void
    {
        Notification::fake();
        $feedback = $this->feedback();

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'resolved'])
            ->assertStatus(302);

        $this->assertSame('resolved', $feedback->fresh()->status);

        Notification::assertSentTo(
            $this->author,
            FeedbackResolvedNotification::class,
            function (FeedbackResolvedNotification $notification): bool {
                $mail = $notification->toMail($this->author);

                return str_contains($mail->subject, 'resolved')
                    && str_contains(implode(' ', $mail->introLines), 'bookmark button')
                    && str_contains((string) $mail->actionText, 'View My Feedback')
                    && $mail->actionUrl === route('user.feedback');
            }
        );
    }

    public function test_other_feedback_statuses_do_not_email(): void
    {
        Notification::fake();
        $feedback = $this->feedback();

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'in_review']);

        Notification::assertNothingSent();
    }

    public function test_guest_feedback_resolution_sends_no_email(): void
    {
        Notification::fake();
        $feedback = $this->feedback(['user_id' => null]);

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'resolved']);

        Notification::assertNothingSent();
    }

    public function test_repeat_resolution_does_not_email_again(): void
    {
        Notification::fake();
        $feedback = $this->feedback(['status' => 'resolved']);

        $this->actingAs($this->admin)
            ->patch(route('admin.feedback.status', $feedback), ['status' => 'resolved']);

        Notification::assertNothingSent();
    }

    public function test_approval_email_renders_through_the_configured_mailer(): void
    {
        $content = $this->submission();
        $transport = $this->app['mailer']->getSymfonyTransport();

        $this->assertInstanceOf(\Illuminate\Mail\Transport\ArrayTransport::class, $transport);

        $this->author->notify(new SubmissionApprovedNotification($content));

        $this->assertCount(1, $transport->messages());

        $message = $transport->messages()->first();
        $email = $message->getOriginalMessage();

        $this->assertStringContainsString('published', (string) $email->getSubject());
        $this->assertStringContainsString($this->author->email, $email->getTo()[0]->getAddress());
        $this->assertStringContainsString('Fan Submission', (string) $email->getHtmlBody());
    }
}
