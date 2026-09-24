@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Add Admin User') }}</h2>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-table-card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="form-control" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="form-control" autocomplete="new-password" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <div class="mb-4 fh-adm-page-head">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required />
                        </div>

                        <div class="mb-4 fh-adm-page-head">
                            <x-input-label :value="__('Role')" />
                            <input type="hidden" name="roles[]" value="admin">
                            <span class="badge bg-primary">Admin</span>
                            <div class="form-text">{{ __('This form creates admin accounts only. The Admin role is assigned automatically.') }}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                            <x-primary-button>{{ __('Create Admin') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card fh-adm-table-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Notes') }}</h6></div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.875rem;">
                        <li class="mb-2">{{ __('Use this form only to create another admin.') }}</li>
                        <li class="mb-2">{{ __('Registered users sign up themselves on the public site.') }}</li>
                        <li class="mb-0">{{ __('The password is hashed and never shown in plain text.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
