<x-auth-shell title="Verify your email" subtitle="We sent you a link. Check your inbox to get started.">
@if(session('status') == 'verification-link-sent')
<div class="fh-auth-status" style="margin-bottom:20px;">A new verification link has been sent to your email.</div>
@endif
<form method="POST" action="{{ route('verification.send') }}" style="margin-bottom:16px;">
@csrf
<button type="submit" class="fh-auth-submit">Resend verification email <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
</form>
<form method="POST" action="{{ route('logout') }}">
@csrf
<button type="submit" class="fh-auth-submit" style="background:transparent;border:1px solid #343845;color:var(--auth-muted);">Log out</button>
</form>
</x-auth-shell>
