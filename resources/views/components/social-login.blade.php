@error('social')
    <p class="fh-auth-status" role="alert">{{ $message }}</p>
@enderror
@if($providers = \App\Support\SocialLogin::providers())
    <div class="fh-auth-social" aria-label="Sign-in options">
        @foreach($providers as $provider => $label)
            <a class="fh-auth-social-button" href="{{ route('social.redirect', $provider) }}">
                <i class="bi bi-{{ $provider }}" aria-hidden="true"></i> Continue with {{ $label }}
            </a>
        @endforeach
    </div>
    <p class="fh-auth-social-divider">or continue with email</p>
@endif
