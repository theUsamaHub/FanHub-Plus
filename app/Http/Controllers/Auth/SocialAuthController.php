<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\SocialLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        return $this->start($request, $provider, null);
    }

    public function connect(Request $request, string $provider): RedirectResponse
    {
        return $this->start($request, $provider, $request->user()->id);
    }

    private function start(Request $request, string $provider, ?int $userId): RedirectResponse
    {
        abort_unless(SocialLogin::enabled($provider), 404);
        $request->session()->put('social_login', [
            'provider' => $provider, 'user_id' => $userId, 'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        $driver = Socialite::driver($provider);
        if ($provider === 'discord') {
            $driver->withConsent();
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(SocialLogin::enabled($provider), 404);
        $flow = $request->session()->pull('social_login');
        $destination = $request->user() ? 'profile.edit' : 'login';
        if (! $flow || $flow['provider'] !== $provider || $flow['expires_at'] < now()->timestamp
            || $flow['user_id'] !== $request->user()?->id) {
            return redirect()->route($destination)->withErrors(['social' => 'Your sign-in session expired. Please try again.']);
        }
        if ($request->has('error')) {
            $request->session()->forget('state');

            return redirect()->route($destination)->withErrors(['social' => 'Sign-in was cancelled. You can try again or use your email and password.']);
        }

        try {
            // Socialite validates the session-bound OAuth state before exchanging the code.
            $identity = Socialite::driver($provider)->user();
            $providerId = (string) $identity->getId();
            $email = Str::lower(trim((string) $identity->getEmail()));
            $verified = $provider === 'google'
                ? ($identity->user['email_verified'] ?? $identity->user['verified_email'] ?? false)
                : ($identity->user['verified'] ?? false);
            if ($providerId === '' || strlen($providerId) > 255) {
                throw ValidationException::withMessages(['social' => 'The provider did not return a valid account. Please try again.']);
            }

            $created = false;
            $user = DB::transaction(function () use ($provider, $providerId, $email, $verified, $identity, $flow, &$created) {
                $account = SocialAccount::where('provider', $provider)->where('provider_user_id', $providerId)->first();
                if ($flow['user_id'] !== null) {
                    $user = User::findOrFail($flow['user_id']);
                    if (($account && $account->user_id !== $user->id)
                        || $user->socialAccounts()->where('provider', $provider)->where('provider_user_id', '!=', $providerId)->exists()) {
                        throw ValidationException::withMessages(['social' => 'This provider is already connected to an account.']);
                    }
                    if (! $account) {
                        $user->socialAccounts()->create(['provider' => $provider, 'provider_user_id' => $providerId]);
                    }

                    return $user;
                }
                // Stable provider IDs authenticate returning users, never a mutable email address.
                if ($account) {
                    return $account->user;
                }
                if ($verified !== true || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                    throw ValidationException::withMessages(['social' => 'Verify your email with the provider first, or register using your email and password.']);
                }
                if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
                    throw ValidationException::withMessages(['social' => 'An account with this email already exists. Log in with your password, then connect this provider from your profile.']);
                }
                $user = User::create([
                    'name' => Str::limit(trim((string) $identity->getName()) ?: 'FanHub member', 255, ''),
                    'email' => $email,
                    // An unknown random password keeps existing password-required flows compatible.
                    // Members can use Forgot password to set their own password later.
                    'password' => Str::random(64),
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                Role::firstOrCreate(['slug' => 'registered-user'], ['name' => 'Registered User']);
                $user->assignRole('registered-user');
                $user->socialAccounts()->create(['provider' => $provider, 'provider_user_id' => $providerId]);
                $created = true;

                return $user;
            });
        } catch (ValidationException $exception) {
            return redirect()->route($destination)->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            // Provider exceptions may contain access tokens or client secrets. Never log their body.
            Log::warning('Social sign-in failed', ['provider' => $provider, 'exception' => get_class($exception)]);

            return redirect()->route($destination)->withErrors(['social' => 'We could not complete sign-in. Please try again or use your email and password.']);
        }

        if ($flow['user_id'] !== null) {
            return redirect()->route('profile.edit')->with('social_status', SocialLogin::PROVIDERS[$provider].' connected.');
        }
        if ($created) {
            event(new Registered($user));
        }
        Auth::login($user);
        $request->session()->regenerate();

        return $user->requiresOnboarding()
            ? redirect()->route('onboarding.create')
            : redirect()->intended(route('dashboard', absolute: false));
    }
}
