<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;
use Mockery;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (['google', 'discord'] as $provider) {
            config(["services.$provider" => [
                'client_id' => 'test-client', 'client_secret' => 'test-secret',
                'redirect' => "http://localhost/auth/$provider/callback",
            ]]);
        }
    }

    private function flow(string $provider = 'google', ?int $userId = null): array
    {
        return ['social_login' => ['provider' => $provider, 'user_id' => $userId, 'expires_at' => now()->addMinutes(10)->timestamp]];
    }

    private function identity(string $provider = 'google', string $id = '123', ?string $email = 'fan@example.com', bool $verified = true): void
    {
        $identity = (new ProviderUser)->setRaw([
            'email_verified' => $verified, 'verified' => $verified,
        ])->map(['id' => $id, 'name' => 'Anime Fan', 'email' => $email]);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($identity);
        Socialite::shouldReceive('driver')->with($provider)->once()->andReturn($driver);
    }

    public function test_buttons_are_only_shown_for_configured_providers(): void
    {
        $this->get('/login')->assertOk()->assertSee('Continue with Google')->assertSee('Continue with Discord');
        $this->get('/register')->assertOk()->assertSee('Continue with Google');
        config(['services.google.client_secret' => null, 'services.discord.client_id' => null]);
        $this->get('/login')->assertDontSee('Continue with Google')->assertDontSee('Continue with Discord');
        $this->get('/auth/google/redirect')->assertNotFound();
        $this->get('/auth/github/redirect')->assertNotFound();
    }

    public function test_live_host_preserves_deployment_path_in_login_links(): void
    {
        $base = 'https://fanhubplus.infinityfree.io/FanHub-Plus/public';
        $this->get('https://fanhubplus.infinityfree.io/login')->assertOk()
            ->assertSee($base.'/auth/google/redirect', false)
            ->assertSee($base.'/auth/discord/redirect', false)
            ->assertSee($base.'/login', false);
        $this->get('http://localhost/login')->assertOk()
            ->assertDontSee($base, false);
    }

    public function test_live_provider_redirect_uses_full_callback_path(): void
    {
        foreach (['google', 'discord'] as $provider) {
            $response = $this->get("https://fanhubplus.infinityfree.io/auth/$provider/redirect")->assertRedirect();
            parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
            $this->assertSame("https://fanhubplus.infinityfree.io/FanHub-Plus/public/auth/$provider/callback", $query['redirect_uri']);
        }
    }

    public function test_live_callback_error_returns_to_subdirectory_login(): void
    {
        $this->get('https://fanhubplus.infinityfree.io/auth/google/callback')
            ->assertRedirect('https://fanhubplus.infinityfree.io/FanHub-Plus/public/login');
    }

    public function test_real_drivers_generate_state_and_expected_scopes(): void
    {
        foreach (['google' => 'accounts.google.com', 'discord' => 'discord.com'] as $provider => $host) {
            $response = $this->get("/auth/$provider/redirect")->assertRedirect();
            $url = $response->headers->get('Location');
            $this->assertSame($host, parse_url($url, PHP_URL_HOST));
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            $this->assertNotEmpty($query['state']);
            $this->assertStringContainsString('email', $query['scope']);
            $this->assertSame(config("services.$provider.redirect"), $query['redirect_uri']);
            $response->assertSessionHas('state', $query['state']);
            if ($provider === 'discord') {
                $this->assertNotSame('none', $query['prompt'] ?? null);
            }
        }
    }

    public function test_google_registration_uses_real_token_and_user_mapping(): void
    {
        $driver = Socialite::driver('google');
        $driver->setHttpClient(new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'test-token', 'token_type' => 'Bearer', 'expires_in' => 3600])),
            new Response(200, [], json_encode(['sub' => 'google-123', 'name' => 'Anime Fan', 'email' => 'fan@example.com', 'email_verified' => true])),
        ]))]));
        Socialite::shouldReceive('driver')->with('google')->andReturnUsing(fn () => $driver->setRequest(request()));
        $this->withSession($this->flow() + ['state' => 'valid-state'])
            ->get('/auth/google/callback?state=valid-state&code=test-code')->assertRedirect(route('onboarding.create'));
        $user = User::where('email', 'fan@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('registered-user'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'google', 'provider_user_id' => 'google-123']);
        $this->assertNotSame('test-token', $user->password);
    }

    public function test_discord_registration_uses_real_provider_mapping(): void
    {
        $driver = Socialite::driver('discord');
        $driver->setHttpClient(new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'test-token', 'token_type' => 'Bearer', 'expires_in' => 3600])),
            new Response(200, [], json_encode(['id' => 'discord-123', 'username' => 'Anime Fan', 'discriminator' => '0', 'email' => 'fan@example.com', 'verified' => true])),
        ]))]));
        Socialite::shouldReceive('driver')->with('discord')->andReturnUsing(fn () => $driver->setRequest(request()));
        $this->withSession($this->flow('discord') + ['state' => 'valid-state'])
            ->get('/auth/discord/callback?state=valid-state&code=test-code')->assertRedirect(route('onboarding.create'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('social_accounts', ['provider' => 'discord', 'provider_user_id' => 'discord-123']);
    }

    public function test_real_driver_rejects_invalid_oauth_state(): void
    {
        // Empty queue also guarantees no token request can succeed.
        $driver = Socialite::driver('google');
        $driver->setHttpClient(new Client(['handler' => HandlerStack::create(new MockHandler([]))]));
        Socialite::shouldReceive('driver')->with('google')->andReturnUsing(fn () => $driver->setRequest(request()));
        $this->withSession($this->flow() + ['state' => 'expected'])
            ->get('/auth/google/callback?state=wrong&code=test')->assertRedirect(route('login'))->assertSessionHasErrors('social');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_returning_identity_signs_in_same_user_even_when_provider_email_changes(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed_at' => now()]);
        $user->socialAccounts()->create(['provider' => 'google', 'provider_user_id' => '123']);
        $this->identity(email: 'changed@example.com');
        $this->withSession($this->flow())->get('/auth/google/callback')->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_email_match_never_automatically_links_an_existing_account(): void
    {
        User::factory()->create(['email' => 'fan@example.com']);
        $this->identity(email: 'FAN@example.com');
        $this->withSession($this->flow())->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_unverified_email_cannot_register(): void
    {
        $this->identity(verified: false);
        $this->withSession($this->flow())->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_missing_discord_email_cannot_register(): void
    {
        $this->identity('discord', email: null);
        $this->withSession($this->flow('discord'))->get('/auth/discord/callback')->assertSessionHasErrors('social');
        $this->assertGuest();
    }

    public function test_cancelled_missing_expired_and_wrong_provider_flows_fail_cleanly(): void
    {
        $this->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->withSession($this->flow())->get('/auth/google/callback?error=access_denied')->assertSessionHasErrors('social');
        $expired = $this->flow();
        $expired['social_login']['expires_at'] = now()->subMinute()->timestamp;
        $this->withSession($expired)->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->withSession($this->flow('discord'))->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_connect_requires_password_confirmation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/auth/google/connect')->assertRedirect(route('password.confirm'));
        $this->withSession(['auth.password_confirmed_at' => time()])->get('/auth/google/connect')->assertRedirect()
            ->assertSessionHas('social_login.user_id', $user->id);
    }

    public function test_authenticated_member_can_connect_a_provider_without_changing_email(): void
    {
        $user = User::factory()->create();
        $this->identity();
        $this->actingAs($user)->withSession($this->flow(userId: $user->id))
            ->get('/auth/google/callback')->assertRedirect(route('profile.edit'))->assertSessionHas('social_status');
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'google']);
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_member_cannot_connect_someone_elses_identity(): void
    {
        $owner = User::factory()->create();
        $owner->socialAccounts()->create(['provider' => 'google', 'provider_user_id' => '123']);
        $user = User::factory()->create();
        $this->identity();
        $this->actingAs($user)->withSession($this->flow(userId: $user->id))
            ->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertDatabaseHas('social_accounts', ['user_id' => $owner->id]);
    }

    public function test_changed_session_user_cannot_finish_linking(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($other)->withSession($this->flow(userId: $user->id))
            ->get('/auth/google/callback')->assertSessionHasErrors('social');
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_provider_failure_does_not_expose_secrets(): void
    {
        Socialite::shouldReceive('driver')->with('google')->andThrow(new \RuntimeException('secret-token'));
        $this->withSession($this->flow())->get('/auth/google/callback')->assertSessionHasErrors('social')->assertDontSee('secret-token');
        $this->assertGuest();
    }

    public function test_member_profile_shows_connection_status_and_password_guidance(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed_at' => now()]);
        $user->socialAccounts()->create(['provider' => 'google', 'provider_user_id' => 'profile-google']);
        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('Connected accounts')->assertSee('Linked to your FanHub account')
            ->assertSee('Connect Discord')->assertSee('Joined with Google or Discord?')
            ->assertSee('Log in → Forgot password')->assertSee('Current password');
    }

    public function test_password_member_does_not_see_provider_password_note(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['onboarding_completed_at' => now()]);
        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('Connect Google')->assertSee('Your FanHub password')
            ->assertDontSee('Joined with Google or Discord?');
    }
}
