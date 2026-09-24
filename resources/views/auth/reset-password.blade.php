<x-auth-shell title="Reset password" subtitle="Choose a new password for your account.">
<form method="POST" action="{{ route('password.store') }}">
@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<x-auth-field name="email" label="Email address" type="email" autocomplete="username" placeholder="you@example.com" />
<x-auth-field name="password" label="New password" type="password" autocomplete="new-password" placeholder="Create a new password" hint="Use at least 8 characters." minlength="8" />
<x-auth-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" placeholder="Re-enter your password" minlength="8" />
<button type="submit" class="fh-auth-submit">Reset password <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
<p class="fh-auth-switch"><a href="{{ route('login') }}">← Back to login</a></p>
</form>
</x-auth-shell>
