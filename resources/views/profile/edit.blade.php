@php
    $layout = auth()->user()->hasRole('admin') ? 'layouts.app' : 'layouts.user.app';
    $isAdmin = auth()->user()->hasRole('admin');
@endphp
@extends($layout)

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div>
            <h2 class="h4 mb-0 fw-semibold">{{ __('Profile') }}</h2>
            <p class="mb-0" style="font-size: 0.875rem; color: var(--fh-adm-muted);">
                {{ $isAdmin ? __('Account basics for guild command.') : __('Your account and community profile.') }}
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4 fh-adm-profile-card">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card mb-4 fh-adm-profile-card">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card border-danger fh-adm-profile-card">
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card fh-adm-profile-summary">
                <div class="card-body p-4">
                    <h6 class="card-title fw-semibold">{{ __('Profile Summary') }}</h6>
                    <div class="fh-adm-profile-rule"></div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="fh-adm-avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            @if ($user->profile?->avatarMedia)
                                <img src="{{ $user->profile->avatarMedia->url }}" alt="" style="width:64px;height:64px;">
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="ms-3">
                            <div class="fw-semibold">{{ Auth::user()->name }}</div>
                            <div style="font-size: 0.875rem; color: var(--fh-adm-muted);">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <div class="fh-adm-profile-rule"></div>
                    <small style="color: var(--fh-adm-dim);">
                        {{ __('Member since') }} {{ Auth::user()->created_at->format('M Y') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection
