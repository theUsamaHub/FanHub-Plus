# Google and Discord sign-in

Development branch: `codex/social-sign-in`. Do not upload this feature to the competition deployment until it has been tested with real provider credentials.

## What is implemented

- Google and Discord on login and registration screens, visible only when that provider's client ID, secret, and callback URL are configured.
- Laravel Socialite with session-bound OAuth state validation and a ten-minute, single-use flow.
- A new `social_accounts` table, unique by provider + provider user ID and by user + provider. Existing migrations, passwords, and profiles remain intact. Tokens are not stored.
- New accounts require a verified provider email and receive only the registered-user role. They continue to fandom onboarding.
- Returning accounts are identified by provider user ID, not email. Provider email changes do not change the FanHub email.
- Existing email matches are not automatically merged. Sign in with the existing password, visit Profile, confirm the password, and connect the provider explicitly. A provider already attached to another member cannot be connected.
- Provider-created users get an unknown random password. Use Forgot password to set a personal password before password-required actions, including connecting a second provider or deleting the account. Working outgoing password-reset email is therefore needed.
- This adds browser sign-in; the existing mobile/API authentication endpoints are unchanged. Disconnecting providers is outside this first release.

## Local setup

```powershell
composer install
php artisan migrate
npm install
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan serve
```

The migration only adds the `social_accounts` table. Never use `migrate:fresh` against existing data.

Add these to your local `.env` (fill the secrets privately):

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI="${APP_URL}/auth/discord/callback"
```

Set `APP_URL` above these variables, without a trailing slash. Both callback settings expand `${APP_URL}` automatically. Register the full expanded callback URLs in the provider consoles; those consoles do not expand environment variables. Locally these are `http://localhost:8000/auth/google/callback` and `http://localhost:8000/auth/discord/callback`.

Use `localhost:8000` consistently rather than alternating with `127.0.0.1`; OAuth state relies on the same browser session cookie returning to the callback. Clear configuration cache after changing `.env`.

## Provider applications

### Google

1. Create/select a project in [Google Cloud Console](https://console.cloud.google.com/).
2. Configure Google Auth Platform branding/audience and an OAuth client of type **Web application**. Use separate development and production clients.
3. Register the exact callback URL above for development. Configure any test-user access required by the project's audience settings.
4. Copy the client ID and client secret into the matching `.env` variables.
5. For production, configure the public application's homepage, privacy policy, audience, and consent settings. Follow any verification requirements shown by Google.

The flow requests basic profile/email identity, not Gmail access. See [Google's web-server OAuth guide](https://developers.google.com/identity/protocols/oauth2/web-server).

### Discord

1. Create an application in the [Discord Developer Portal](https://discord.com/developers/applications).
2. In OAuth2, register the exact Discord callback URL above.
3. Copy the application client ID and client secret into `.env`. A bot token is not needed.
4. Only `identify` and `email` are requested. No server joining, messages, or bot permissions are required.

See [Discord OAuth2 documentation](https://discord.com/developers/docs/topics/oauth2) and the [Socialite Discord provider](https://socialiteproviders.com/Discord/).

## Production after evaluation

For the current live base path, register and configure these exact URLs:

```dotenv
APP_URL=https://fanhubplus.infinityfree.io/FanHub-Plus/public
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
DISCORD_REDIRECT_URI="${APP_URL}/auth/discord/callback"
SESSION_SECURE_COOKIE=true
APP_DEBUG=false
```

Keep `SESSION_DOMAIN` unset/null for a host-only cookie. These are top-level browser redirects; do not embed provider sign-in inside an iframe. Do not disable state validation to work around session failures.

Deploy the changed PHP and Blade files, new migration/model/controller/helper, composer.json and composer.lock, and the complete rebuilt `public/build` assets plus manifest. Install production dependencies with `composer install --no-dev --optimize-autoloader` on the server or prepare a matching production vendor directory for upload if the host has no Composer access. Uploading application code without the new dependencies will fail.

Back up the live database, run `php artisan migrate --force` in the deployed app, clear route/config/view caches, then configure credentials. If the host cannot execute Artisan, arrange a controlled migration through its supported tooling; do not publish an unauthenticated migration/command-runner URL. Do not upload local `.env`, local sessions, or local cached configuration.

The server must be able to reach Google and Discord token/user endpoints over HTTPS. Actual InfinityFree OAuth round trips have not been tested. A callback block, missing cookies, or outbound request failure is a hosting/configuration issue and needs to be diagnosed from the deployed response.

## Validation before enabling

Automated tests use an isolated in-memory database and mocked provider HTTP responses. They cover real Socialite token/user mapping, OAuth state rejection, provider errors/cancellation, email validation/collisions, account linking, and existing authentication.

```powershell
php artisan test tests/Feature/Auth
```

Then test with real credentials in a normal browser: new Google and Discord accounts; log out and return; cancel consent; connect from an existing member profile; reject linking another member's provider; confirm onboarding; and send a password-reset email to a provider-created account. Confirm both desktop and mobile layouts.

During dependency installation, Composer reported 11 advisories in three existing packages (`laravel/framework`, `league/commonmark`, `league/flysystem`), not the newly added Socialite packages. Schedule dependency remediation separately before production rollout; this branch does not perform a framework upgrade.
