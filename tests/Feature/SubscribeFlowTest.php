<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subscriber;
use App\Models\User;
use App\Notifications\SubscriberWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscribeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribing_creates_subscriber_with_token_and_sends_welcome_mail(): void
    {
        Notification::fake();

        $this->from('/')->post(route('public.subscribe'), [
            'email' => 'fan@example.com',
            'name' => 'Fan Reader',
        ])->assertRedirect('/');

        $subscriber = Subscriber::where('email', 'fan@example.com')->firstOrFail();

        $this->assertNotNull($subscriber->unsubscribe_token);
        $this->assertSame('active', $subscriber->status);
        $this->assertNotNull($subscriber->subscribed_at);
        $this->assertNull($subscriber->unsubscribed_at);

        Notification::assertSentTo($subscriber, SubscriberWelcomeNotification::class);
    }

    public function test_subscribe_validation_errors_use_the_subscribe_error_bag(): void
    {
        $response = $this->from('/')->post(route('public.subscribe'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email'], null, 'subscribe');
        $response->assertSessionDoesntHaveErrors(['email'], null, 'default');
    }

    public function test_duplicate_subscribe_is_idempotent_and_does_not_resend_welcome(): void
    {
        Notification::fake();

        $this->from('/')->post(route('public.subscribe'), ['email' => 'fan@example.com']);
        $this->from('/')->post(route('public.subscribe'), ['email' => 'fan@example.com'])
            ->assertRedirect('/');

        $this->assertSame(1, Subscriber::where('email', 'fan@example.com')->count());
        $this->assertSame('You are already subscribed!', session('success'));

        Notification::assertSentToTimes(
            Subscriber::where('email', 'fan@example.com')->firstOrFail(),
            SubscriberWelcomeNotification::class,
            1
        );
    }

    public function test_unsubscribed_address_can_subscribe_again_and_gets_a_token(): void
    {
        Notification::fake();

        $subscriber = Subscriber::create([
            'email' => 'back@example.com',
            'name' => 'Returning Fan',
            'subscribed_at' => now()->subMonth(),
            'unsubscribed_at' => now()->subWeek(),
            'status' => 'unsubscribed',
            'unsubscribe_token' => null,
        ]);

        $this->from('/')->post(route('public.subscribe'), ['email' => 'back@example.com'])
            ->assertRedirect('/');

        $subscriber->refresh();

        $this->assertSame('active', $subscriber->status);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNotNull($subscriber->unsubscribe_token);

        Notification::assertSentTo($subscriber, SubscriberWelcomeNotification::class);
    }

    public function test_unsubscribe_link_marks_the_subscriber_unsubscribed(): void
    {
        $subscriber = Subscriber::create([
            'email' => 'leave@example.com',
            'name' => 'Leaving Fan',
            'subscribed_at' => now(),
            'status' => 'active',
        ]);

        $this->get(route('unsubscribe', $subscriber->unsubscribe_token))
            ->assertOk()
            ->assertSee('Unsubscribed', false);

        $subscriber->refresh();

        $this->assertSame('unsubscribed', $subscriber->status);
        $this->assertNotNull($subscriber->unsubscribed_at);
        $this->assertNotNull($subscriber->unsubscribe_token);
    }

    public function test_invalid_unsubscribe_token_renders_the_invalid_page(): void
    {
        $this->get(route('unsubscribe', 'not-a-real-token'))
            ->assertOk()
            ->assertSee('Invalid', false);

        $this->assertSame(0, Subscriber::count());
    }

    public function test_profile_newsletter_switch_subscribes_and_unsubscribes(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);

        $this->actingAs($user)->post(route('profile.newsletter-preferences'), [
            'subscribe' => '1',
            'categories' => [$category->id],
            'frequency' => 'weekly',
        ])->assertSessionHasNoErrors();

        $subscriber = Subscriber::where('email', $user->email)->firstOrFail();
        $this->assertSame('active', $subscriber->status);
        $this->assertSame('weekly', $subscriber->getPreferences()['frequency']);
        $this->assertNotNull($subscriber->unsubscribe_token);

        $this->actingAs($user)->post(route('profile.newsletter-preferences'), [])
            ->assertSessionHasNoErrors();

        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
    }

    public function test_profile_unsubscribe_without_a_subscription_is_a_no_op(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.newsletter-preferences'), [])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, Subscriber::count());
    }

    public function test_home_page_renders_the_subscribe_form(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="footer_subscribe_email"', false)
            ->assertSee(route('public.subscribe'), false);
    }

    public function test_subscribing_from_the_footer_form_works(): void
    {
        Notification::fake();

        $this->from(route('home'))->post(route('public.subscribe'), [
            'email' => 'footer@example.com',
        ])->assertRedirect(route('home'));

        $this->assertNotNull(Subscriber::where('email', 'footer@example.com')->first());
        Notification::assertSentTo(
            Subscriber::where('email', 'footer@example.com')->firstOrFail(),
            SubscriberWelcomeNotification::class
        );
    }
}
