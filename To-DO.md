Implementation checkpoint — 2026-09-26

See MEMBER_FEATURE_PROGRESS.md for saved registered-user pages and tests. The main account flows are implemented; final regression/browser verification is in progress. The onboarding and soft-verification requirements below are retained and are the next remaining UX checks.

Onboarding Flow — Logic Summary
1. Fandom-picker modal (post-registration)

Trigger: After a user registers and lands on the dashboard, check whether they've completed onboarding. Track this with a single new field — onboarding_completed_at on user_profiles — set to NULL by default at signup.

Display rule: If onboarding_completed_at is NULL, auto-show a modal listing all fandom categories as checkboxes. If it's already set, never show it again.

Two exits, same result:

Save — user checks their preferred fandoms → these get written into the existing user_favorite_categories pivot table → onboarding_completed_at is stamped with the current time.
Skip — no categories are saved, but onboarding_completed_at is still stamped with the current time.

The key logic point: both paths mark onboarding as done. If Skip didn't also close it out, the modal would keep reappearing on every login, which would feel broken rather than optional. "Not necessary" has to actually mean not necessary — permanently, not just for one session.

Where this lives: One new nullable timestamp column, one small check on the dashboard route (or shared middleware if multiple pages need to trigger it), and one form submission handler that branches on which button was clicked but converges on the same "mark complete" step.

2. Email verification — soft nudge, not a gate

Principle: Verification status should never block access to any route or feature. No middleware restricting pages based on email_verified_at.

Display rule: On every authenticated page, check if the logged-in user's email_verified_at is still NULL. If so, show a persistent but dismissible banner (not a modal, not a blocking screen) prompting them to verify, with a resend-link action.

Dismissibility: The banner should be closeable per session/page-load (client-side toggle) but will reappear on next page load until they actually verify — since we're not storing a "user dismissed this" flag anywhere. That's intentional: it's a gentle, recurring reminder, not a one-time nag they can permanently mute.

No new database fields needed here — Laravel's built-in email_verified_at on users already covers this; we're just choosing not to enforce it and instead surface it as UI guidance.

Why this pairing makes sense together

Both onboarding pieces follow the same philosophy: collect what's useful, but never force it. The fandom picker personalizes the experience if they engage, and does nothing bad if they don't. The verification banner nudges toward account security without punishing users who haven't gotten around to it yet. Neither blocks the core registration → dashboard flow, which keeps your first-run experience frictionless — important for a demo/evaluation context where judges need to move through the app quickly without hitting walls
