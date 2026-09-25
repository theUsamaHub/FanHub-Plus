@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Create Tag') }}</h2>
                    <a href="{{ route('admin.tags.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
                </div>
    </div>

    <div class="row"><div class="col-lg-6">
        <div class="card fh-adm-form-card"><div class="card-body p-4">
            <form action="{{ route('admin.tags.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <x-input-label for="name" :value="__('Tag Name')" />
                    <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.tags.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Create Tag') }}</x-primary-button>
                </div>
            </form>
        </div></div>
    </div></div>
@endsection
