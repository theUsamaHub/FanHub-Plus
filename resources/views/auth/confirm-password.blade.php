<x-auth-shell title="Confirm password" subtitle="This is a secure area. Please confirm your password.">
<form method="POST" action="{{ route('password.confirm') }}">
@csrf
<x-auth-field name="password" label="Password" type="password" autocomplete="current-password" placeholder="Enter your password" />
<button type="submit" class="fh-auth-submit">Confirm <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
</form>
</x-auth-shell>
