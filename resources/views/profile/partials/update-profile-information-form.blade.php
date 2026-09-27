<section>
    <header>
        <h5 class="fw-semibold">{{ __('Profile Information') }}</h5>
        <p class="text-muted mb-0" style="font-size: 0.875rem;">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="form-control" :value="old('email', $user->email)" autocomplete="username" disabled readonly />
            <small class="text-muted">{{ __('Email is locked after account creation and cannot be changed.') }}</small>
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted mb-1" style="font-size: 0.875rem;">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.875rem;">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success py-1" style="font-size: 0.875rem;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if ($user->hasRole('admin'))
            <div class="mb-4">
                <x-input-label for="avatar" :value="__('Avatar image')" />
                <div class="d-flex align-items-center gap-3 mb-2">
                    @if ($user->profile?->avatarMedia?->url)
                        <img src="{{ $user->profile->avatarMedia->url }}" alt="" class="fh-adm-avatar" style="width:56px;height:56px;object-fit:cover;">
                    @else
                        <div class="fh-adm-avatar" style="width:56px;height:56px;font-size:1.2rem;">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                    <input type="file" class="form-control" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>
                <small class="text-muted">{{ __('JPG, PNG, GIF, or WebP. Max 2MB. Uploaded to media storage.') }}</small>
                <x-input-error class="mt-1" :messages="$errors->get('avatar')" />

                @if ($user->profile?->avatar_media_id)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="remove_avatar">
                        <label class="form-check-label" for="remove_avatar">{{ __('Remove current avatar') }}</label>
                    </div>
                @endif
            </div>
        @else
            <div class="mb-3">
                <x-input-label for="display_name" :value="__('Display name')" />
                <x-text-input id="display_name" name="display_name" type="text" class="form-control" :value="old('display_name', $user->profile?->display_name)" maxlength="100" />
                <x-input-error class="mt-1" :messages="$errors->get('display_name')" />
            </div>

            <div class="mb-3">
                <x-input-label for="bio" :value="__('Bio')" />
                <textarea id="bio" name="bio" rows="3" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $user->profile?->bio) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('bio')" />
            </div>

            </div>
        @endif

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <span
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-muted"
                    style="font-size: 0.875rem;"
                >{{ __('Saved.') }}</span>
            @endif
        </div>
    </form>
</section>
