# FanHub Plus: sign-in, account linking, and password flow

Documented: October 1, 2026
Feature branch: `codex/social-sign-in`

This document describes the currently implemented behavior. Google and Discord require provider credentials before they appear on the website. The feature has not been deployed to the competition website or tested with real provider accounts.

For developer setup, provider registration, deployment instructions, and callback URLs, see [Social sign-in setup](social-sign-in.md).

## 1. Available sign-in options

Members can use:

- FanHub email and password.
- Google, when configured.
- Discord, when configured.

Login and registration both offer the same provider flow. FanHub decides whether to create an account or sign in an existing member after the provider responds.

Each provider button appears only when its client ID, client secret, and callback URL are configured. Email/password authentication remains available independently.

## 2. New member registering through Google or Discord

1. The visitor opens Log in or Sign up.
2. They select Continue with Google or Continue with Discord.
3. FanHub starts a session-bound sign-in flow and redirects to the provider.
4. The visitor authenticates with the provider and approves the requested identity access.
5. The provider redirects their browser to FanHub's callback URL.
6. FanHub validates the flow, the provider, and OAuth state. The flow must finish within ten minutes and in the same browser session.
7. FanHub checks whether that provider's unique account ID is already connected.
8. If no connection exists, a new account requires a valid, verified provider email that is not already used by a FanHub member.
9. FanHub creates the member, records the provider connection, marks that email verified, and assigns only the registered-user role.
10. FanHub signs them in, regenerates their session ID, and sends them to the existing fandom onboarding flow.

The existing onboarding feature controls fandom selection and completion. Social sign-in does not create a second onboarding system.

FanHub never receives the person's Google or Discord password. Provider access tokens are not stored in the social-accounts table.

## 3. Returning member using a connected provider

1. The member selects the provider they previously connected.
2. They authenticate with that provider.
3. FanHub looks up the combination of provider name and provider account ID.
4. FanHub signs them into the associated member account.
5. Members requiring onboarding go to onboarding; others continue to the intended page or dashboard.

Their profile, favorites, bookmarks, and other saved content remain associated with the same FanHub account.

Identification uses the provider account ID rather than its email address. Changing an email at Google or Discord does not automatically change the FanHub profile email or create a new member account for an already connected identity.

## 4. Existing email/password member tries provider login

If the provider is not connected but its email matches an existing FanHub account, FanHub does not automatically merge or sign in that account.

The member sees instructions to sign in with their existing password and connect the provider from Profile. If they have forgotten that password, they must use Forgot password first.

This avoids granting access to an existing account based only on a matching email address.

## 5. Connecting Google or Discord from Profile

1. The member signs in to FanHub.
2. They open Profile and find Connected sign-in accounts.
3. They choose Connect Google or Connect Discord.
4. FanHub requests their current FanHub password if recent password confirmation is required.
5. The member authenticates with the selected provider.
6. FanHub validates the callback and confirms that the same FanHub member is still signed in.
7. FanHub records the connection and returns to Profile with a confirmation message.

After connection, the member can use either their FanHub password or the connected provider.

Rules:

- Each FanHub member can connect one Google account and one Discord account.
- A provider identity cannot belong to two FanHub members.
- A different account from an already connected provider cannot replace it through this flow.
- Reconnecting the same identity does not create a duplicate record.
- Explicit connection does not overwrite the member's FanHub email, password, or profile name. The provider email does not have to match because both accounts are authenticated separately.
- Disconnecting or replacing providers is not implemented in this release.

## 6. Profile: Update password

The existing form requires all three fields:

1. Current FanHub password.
2. New FanHub password.
3. Confirmation of the new password.

FanHub validates the current password, checks the new password against its password rules, and requires the confirmation to match. It stores a hash of the new password and returns to the previous page with `password-updated` status.

If validation fails, the password is not changed and the form displays validation errors.

### Scenario A: registered with email and password

The member knows their FanHub password and can use the Profile form directly. If they forget it, they use the emailed password-reset flow instead.

### Scenario B: email/password account with a provider connected

The member still uses their existing FanHub password as Current password, even if they used Google or Discord to sign in today.

Updating the FanHub password does not disconnect either provider. It does not change the Google or Discord password.

### Scenario C: registered through a provider and never set a FanHub password

The application creates an unknown random password for these accounts to remain compatible with the existing non-null password column and password-required features. The member is not given that random password.

They cannot complete the Profile Update password form until they set a personal FanHub password through Forgot password.

They must not enter their Google or Discord password into FanHub.

### Scenario D: registered through a provider and later set a FanHub password

Once the member sets a password through the reset email, they can use email/password login and update their password through Profile like any other member. Provider sign-in also remains available.

### Password scenario summary

| Member situation | Action |
| --- | --- |
| Knows their current FanHub password | Use Profile → Update password. |
| Forgot their FanHub password | Use Forgot password and the emailed reset link. |
| Joined through Google/Discord and has no personal FanHub password yet | Use Forgot password to set the first personal password. |
| Uses a provider but previously set a FanHub password | Use that FanHub password in the Profile form. |
| Wants to change their Google/Discord password | Change it in the provider's account settings. |

## 7. Forgot password: first password or recovery

1. The member opens Forgot password while signed out. If necessary, they sign out first; password-reset routes use guest middleware.
2. They enter the email currently recorded in their FanHub profile.
3. FanHub sends a password-reset link using the configured mail service.
4. They open the email link.
5. They enter and confirm a new FanHub password.
6. FanHub validates the email and reset token, stores the password hash, rotates the remember token, and redirects to Log in.
7. They sign in using the FanHub email and new password, or continue using a connected provider.

The reset flow does not require knowing the old password. It requires access to the FanHub email inbox and a valid reset link. If the link is invalid or expired, request another one.

Working outgoing email is essential. A mail configuration that only writes messages to a local log will not deliver a reset email to a real member's inbox.

Changing or resetting a FanHub password preserves connected providers and saved member content. Do not assume all existing sessions are logged out: the Profile update controller does not explicitly invalidate other sessions.

## 8. Other password-required actions

Connecting another provider requires password confirmation. Account deletion also requires the current FanHub password.

Provider-created members must therefore set their own FanHub password before using those actions. When a FanHub user is deleted, their social-account rows are removed by the database relationship's cascading delete. Their actual Google and Discord accounts are not deleted.

## 9. Failure and recovery behavior

| Situation | Current behavior |
| --- | --- |
| Provider credentials are missing | The provider button is hidden; its route is unavailable. |
| Member cancels provider consent | Returns with a cancellation message; no new account is created. |
| Flow is missing, expired, or belongs to a different provider/session user | Sign-in/linking is rejected; start again. |
| OAuth state is invalid | Sign-in/linking fails without authenticating the member. |
| New provider account has no valid verified email | Registration is rejected; verify the provider email or use email/password registration. |
| Email matches an existing unconnected FanHub account | Sign in to that account first, then explicitly connect the provider. |
| Provider identity belongs to another FanHub member | Connection is rejected. |
| Provider request fails | Shows a generic retry message rather than exposing credentials or provider response details. |
| Wrong current password or mismatched new-password confirmation | Profile password update fails with validation errors. |
| Reset email does not arrive | Check the inbox/spam folder and configured mail delivery; request another link if needed. |

## 10. Current interface limitations

- Provider-created members still see the ordinary Update password form before they have set a personal password.
- The user profile shows themed provider rows with connected badges or Connect buttons. Members with a linked provider see a password-setup note above the Update password form, with a shortcut from Connected accounts. Members without a linked provider do not see that note.
- The note uses conditional wording because a provider connection alone does not establish whether a personal password has been set. An automatic Set a FanHub password versus Update password interface is not implemented.
- Forgot-password routes are guest-only. A signed-in member may need to sign out before following the reset instructions.
- There is no provider-disconnect interface in this release.
- Browser authentication is implemented; provider login for the existing API/mobile endpoints is not implemented.

Potential follow-up: track whether a member has set a personal password and show a dedicated, clearly labeled setup flow. This is a proposed improvement, not existing behavior.

## 11. Activation and testing status

Completed locally:

- Feature implemented on `codex/social-sign-in`.
- Google and Discord dependencies installed.
- Additive `social_accounts` migration created.
- 53 authentication, profile, and onboarding tests passed using isolated test data and mocked provider responses.
- Frontend build and Blade compilation succeeded.

Still required:

1. Start local MySQL in XAMPP. The attempted local migration could not connect to MySQL.
2. Run `php artisan migrate` against the intended local database.
3. Create Google and Discord OAuth applications and configure their credentials privately in `.env`.
4. Configure the exact callback URLs described in [the setup guide](social-sign-in.md).
5. Run `php artisan config:clear` after changing configuration.
6. Test real provider registration, returning sign-in, linking, cancellation, and password-reset email delivery.
7. Review and deploy separately after deciding to update the competition website.

No production deployment or real-provider authentication has been verified as part of this implementation. The setup guide also records dependency advisories found during installation.
