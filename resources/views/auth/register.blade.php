<x-auth-shell title="Find your people." subtitle="Create your account. Make yourself at home.">
<form method="POST" action="{{ route('register') }}">
@csrf
<x-auth-field name="name" label="Your name" autocomplete="name" placeholder="What should we call you?" maxlength="255" />
<x-auth-field name="email" label="Email address" type="email" autocomplete="username" placeholder="you@example.com" maxlength="255" />
<x-auth-field name="password" label="Password" type="password" autocomplete="new-password" placeholder="Create a password" hint="Use at least 8 characters." minlength="8" />
<x-auth-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" placeholder="Re-enter your password" minlength="8" />
<button type="submit" class="fh-auth-submit">Create account <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
<p class="fh-auth-switch">Already part of the fandom? <a href="{{ route('login') }}">Log in</a></p>
</form>
</x-auth-shell>
