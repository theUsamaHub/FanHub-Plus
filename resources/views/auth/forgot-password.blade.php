<x-auth-shell title="Forgot password?" subtitle="No worries. We will send you a reset link.">
<x-auth-session-status class="fh-auth-status" :status="session('status')" />
<form method="POST" action="{{ route('password.email') }}">
@csrf
<x-auth-field name="email" label="Email address" type="email" autocomplete="username" placeholder="you@example.com" />
<button type="submit" class="fh-auth-submit">Send reset link <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
<p class="fh-auth-switch"><a href="{{ route('login') }}">← Back to login</a></p>
</form>
</x-auth-shell>
