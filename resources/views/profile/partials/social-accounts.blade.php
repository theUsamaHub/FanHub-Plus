@if($providers = \App\Support\SocialLogin::providers())
    <section aria-labelledby="social-accounts-title">
        <h2 id="social-accounts-title" class="h5">Connected sign-in accounts</h2>
        <p>Connect Google or Discord to sign in next time. Connecting requires password confirmation.</p>
        <p>Joined using a provider? Use <a href="{{ route('password.request') }}">Forgot password</a> to set a password before connecting another account, changing your password, or deleting your account.</p>
        @if(session('social_status'))<p role="status">{{ session('social_status') }}</p>@endif
        @error('social')<p role="alert">{{ $message }}</p>@enderror
        @foreach($providers as $provider => $label)
            <div class="mb-3">
                @if($user->socialAccounts->contains('provider', $provider))
                    <span>{{ $label }} connected</span>
                @else
                    <a class="btn btn-outline-primary" href="{{ route('social.connect', $provider) }}">Connect {{ $label }}</a>
                @endif
            </div>
        @endforeach
    </section>
@endif
