<section>
    <header>
        <h5 class="fw-semibold">{{ __('Profile Information') }}</h5>
        <p class="text-muted mb-0" style="font-size: 0.875rem;">
            {{ __("Update your account's profile information and email address.") }}
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
            <x-text-input id="email" name="email" type="email" class="form-control" :value="old('email', $user->email)" required autocomplete="username" />
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
                    @if ($user->profile?->avatarMedia)
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-input-label for="avatar" :value="__('Avatar image')" />
                    <input type="file" class="form-control" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-muted">{{ __('Or choose from library below. Max 2MB.') }}</small>
                    <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
                </div>
                <div class="col-md-6 mb-3">
                    <x-input-label for="avatar_media_id" :value="__('Avatar from library')" />
                    <select name="avatar_media_id" id="avatar_media_id" class="form-select">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($images as $image)
                            <option value="{{ $image->id }}" @selected((int) old('avatar_media_id', $user->profile?->avatar_media_id) === $image->id)>{{ $image->original_filename }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('avatar_media_id')" />
                </div>
                <div class="col-md-6 mb-3">
                    <x-input-label for="theme_preference" :value="__('Theme')" />
                    <select name="theme_preference" id="theme_preference" class="form-select">
                        @foreach (['system', 'light', 'dark'] as $theme)
                            <option value="{{ $theme }}" @selected(old('theme_preference', $user->profile?->theme_preference ?? 'system') === $theme)>{{ ucfirst($theme) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('theme_preference')" />
                </div>
                <div class="col-md-6 mb-3">
                    <x-input-label for="font_size_preference" :value="__('Font size')" />
                    <select name="font_size_preference" id="font_size_preference" class="form-select">
                        @foreach (['small', 'medium', 'large'] as $size)
                            <option value="{{ $size }}" @selected(old('font_size_preference', $user->profile?->font_size_preference ?? 'medium') === $size)>{{ ucfirst($size) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('font_size_preference')" />
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
