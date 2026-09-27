<?php

namespace Tests\Feature\Admin;

use App\Mail\NewsletterMail;
use App\Models\Category;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Transport\ArrayTransport;
use Tests\TestCase;

class NewsletterTest extends TestCase
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

    private function subscriber(string $email, array $overrides = []): Subscriber
    {
        return Subscriber::create(array_merge([
            'email' => $email,
            'name' => 'Fan ' . $email,
            'subscribed_at' => now(),
            'status' => 'active',
        ], $overrides));
    }

    private function newsletter(array $overrides = []): Newsletter
    {
        return Newsletter::create(array_merge([
            'subject' => 'Weekly drop',
            'body' => 'Hi {name}, new stories are live. Unsubscribe: {unsubscribe_url}',
            'type' => 'custom',
            'recipient_filters' => [],
            'recipient_count' => 0,
            'status' => 'draft',
            'sent_by' => $this->admin->id,
        ], $overrides));
    }

    public function test_subscriber_show_page_renders(): void
    {
        $subscriber = $this->subscriber('show@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.subscribers.show', $subscriber))
            ->assertOk()
            ->assertSee($subscriber->email)
            ->assertSee('Category Preferences');
    }

    public function test_newsletter_create_page_honours_category_prefill(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);

        $this->actingAs($this->admin)
            ->get(route('admin.newsletters.create', ['categories' => [$category->id]]))
            ->assertOk()
            ->assertSee('value="' . $category->id . '" selected', false);
    }

    public function test_admin_can_create_edit_and_delete_a_draft(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.newsletters.store'), [
                'subject' => 'First issue',
                'body' => 'Hello fans',
                'type' => 'custom',
            ])
            ->assertRedirect();

        $newsletter = Newsletter::firstWhere('subject', 'First issue');
        $this->assertNotNull($newsletter);
        $this->assertSame('draft', $newsletter->status);

        $this->actingAs($this->admin)
            ->get(route('admin.newsletters.edit', $newsletter))
            ->assertOk()
            ->assertSee('First issue');

        $this->actingAs($this->admin)
            ->put(route('admin.newsletters.update', $newsletter), [
                'subject' => 'Second issue',
                'body' => 'Hello again',
                'type' => 'custom',
            ])
            ->assertRedirect(route('admin.newsletters.show', $newsletter));

        $this->assertSame('Second issue', $newsletter->fresh()->subject);

        $this->actingAs($this->admin)
            ->delete(route('admin.newsletters.destroy', $newsletter))
            ->assertRedirect(route('admin.newsletters.index'));

        $this->assertNull(Newsletter::find($newsletter->id));
    }

    public function test_sent_newsletter_cannot_be_edited(): void
    {
        $newsletter = $this->newsletter(['status' => 'sent', 'sent_at' => now()]);

        $this->actingAs($this->admin)
            ->get(route('admin.newsletters.edit', $newsletter))
            ->assertRedirect(route('admin.newsletters.show', $newsletter));

        $this->actingAs($this->admin)
            ->from(route('admin.newsletters.show', $newsletter))
            ->put(route('admin.newsletters.update', $newsletter), [
                'subject' => 'Changed',
                'body' => 'Changed body',
            ])
            ->assertSessionHas('error');
    }

    public function test_send_delivers_to_active_subscribers_only(): void
    {
        Mail::fake();

        $active = $this->subscriber('active@example.com');
        $left = $this->subscriber('left@example.com', [
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
            'subscribed_at' => now(),
        ]);
        $newsletter = $this->newsletter();

        $this->actingAs($this->admin)
            ->from(route('admin.newsletters.show', $newsletter))
            ->post(route('admin.newsletters.send', $newsletter))
            ->assertSessionHas('success');

        Mail::assertSent(NewsletterMail::class, 1);

        $newsletter->refresh();
        $this->assertSame('sent', $newsletter->status);
        $this->assertSame(1, $newsletter->sent_count);
        $this->assertSame(0, $newsletter->failed_count);
        $this->assertNotNull($newsletter->sent_at);

        $active->refresh();
        $this->assertSame(1, $active->email_count);
        $this->assertNotNull($active->last_email_sent_at);

        $left->refresh();
        $this->assertSame(0, $left->email_count);
    }

    public function test_send_without_recipients_is_blocked(): void
    {
        Mail::fake();
        $newsletter = $this->newsletter();

        $this->actingAs($this->admin)
            ->from(route('admin.newsletters.show', $newsletter))
            ->post(route('admin.newsletters.send', $newsletter))
            ->assertSessionHas('error');

        Mail::assertNothingSent();

        $this->assertSame('draft', $newsletter->fresh()->status);
    }

    public function test_sending_the_same_newsletter_twice_is_blocked(): void
    {
        Mail::fake();
        $this->subscriber('active@example.com');
        $newsletter = $this->newsletter();

        $this->actingAs($this->admin)
            ->post(route('admin.newsletters.send', $newsletter));

        $this->actingAs($this->admin)
            ->from(route('admin.newsletters.show', $newsletter))
            ->post(route('admin.newsletters.send', $newsletter))
            ->assertSessionHas('error');

        Mail::assertSent(NewsletterMail::class, 1);
    }

    public function test_placeholders_and_unsubscribe_link_are_replaced_in_the_real_email(): void
    {
        $subscriber = $this->subscriber('reader@example.com', ['name' => 'Ayesha']);
        $newsletter = $this->newsletter();
        $transport = $this->app['mailer']->getSymfonyTransport();

        $this->assertInstanceOf(ArrayTransport::class, $transport);

        $this->actingAs($this->admin)
            ->post(route('admin.newsletters.send', $newsletter));

        $this->assertCount(1, $transport->messages());

        $email = $transport->messages()->first()->getOriginalMessage();
        $html = (string) $email->getHtmlBody();

        $this->assertStringContainsString('Ayesha', $html);
        $this->assertStringNotContainsString('{name}', $html);
        $this->assertStringNotContainsString('{unsubscribe_url}', $html);
        $this->assertStringContainsString('/unsubscribe/' . $subscriber->fresh()->unsubscribe_token, $html);
        $this->assertStringContainsString($subscriber->email, $email->getTo()[0]->getAddress());
    }

    public function test_newsletter_index_show_and_preview_pages_render(): void
    {
        $this->subscriber('reader@example.com');
        $newsletter = $this->newsletter();

        $this->actingAs($this->admin)
            ->get(route('admin.newsletters.index'))
            ->assertOk()
            ->assertSee('Weekly drop');

        $this->actingAs($this->admin)
            ->get(route('admin.newsletters.show', $newsletter))
            ->assertOk()
            ->assertSee('Weekly drop')
            ->assertSee('Draft');

        $this->actingAs($this->admin)
            ->get(route('admin.newsletters.preview', $newsletter))
            ->assertOk()
            ->assertSee('Weekly drop');
    }
}
