<x-auth-shell title="Welcome back." subtitle="Your favorite worlds are waiting for you.">
<x-auth-session-status class="fh-auth-status" :status="session('status')" />
<form method="POST" action="{{ route('login') }}">
@csrf
<x-auth-field name="email" label="Email address" type="email" autocomplete="username" placeholder="you@example.com" />
<x-auth-field name="password" label="Password" type="password" autocomplete="current-password" placeholder="Enter your password" />
<div class="fh-auth-options"><label class="fh-auth-remember" for="remember_me"><input type="checkbox" name="remember" id="remember_me" @checked(old('remember'))> Remember me</label>@if(Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot password?</a>@endif</div>
<button type="submit" class="fh-auth-submit">Log in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
<p class="fh-auth-switch">New to the universe? <a href="{{ route('register') }}">Create an account</a></p>
</form>
</x-auth-shell>
