@php
    $connections = $user->socialAccounts->keyBy('provider');
    $providers = array_filter(\App\Support\SocialLogin::PROVIDERS, fn ($provider) => \App\Support\SocialLogin::enabled($provider) || $connections->has($provider), ARRAY_FILTER_USE_KEY);
@endphp
@if($providers)
<section class="member-panel member-connections" aria-labelledby="connected-accounts-title">
    <div class="member-connections__heading">
        <span class="member-connections__symbol" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
        <div><span class="member-kicker">YOUR SIGN-IN OPTIONS</span><h2 id="connected-accounts-title">Connected accounts</h2><p>One FanHub profile. More ways to come back.</p></div>
    </div>
    @if(session('social_status'))<p class="member-connections__notice" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i> {{ session('social_status') }}</p>@endif
    @error('social')<p class="member-connections__notice member-connections__notice--error" role="alert">{{ $message }}</p>@enderror
    <div class="member-connections__list">
        @foreach($providers as $provider => $label)
            @php($connected = $connections->has($provider))
            <div class="member-provider">
                <span class="member-provider__icon" aria-hidden="true"><i class="bi bi-{{ $provider }}"></i></span>
                <div class="member-provider__copy"><h3>{{ $label }}</h3><p>{{ $connected ? 'Linked to your FanHub account' : 'Use your '.$label.' account to sign in' }}</p></div>
                @if($connected)
                    <span class="member-provider__status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Connected</span>
                @else
                    <a class="member-button member-button--quiet member-provider__connect" href="{{ route('social.connect', $provider) }}" aria-label="Connect {{ $label }}">Connect <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                @endif
            </div>
        @endforeach
    </div>
    <p class="member-connections__footnote"><i class="bi bi-lock" aria-hidden="true"></i> Your provider password stays private. Connecting another account requires your FanHub password.</p>
    @if($connections->isNotEmpty())
        <a class="member-connections__password-link" href="#profile-password">Joined with Google or Discord? Read the password setup note <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
    @endif
</section>
@endif
