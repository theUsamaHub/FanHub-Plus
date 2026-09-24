@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Create Category') }}</h2>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-table-card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="name" :value="__('Category Name')" />
                            <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="slug" :value="__('Slug (optional)')" />
                            <x-text-input id="slug" name="slug" type="text" class="form-control" :value="old('slug')" />
                            <small class="text-muted">{{ __('Leave blank to auto-generate from name.') }}</small>
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" maxlength="500" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            <small class="text-muted">{{ __('Maximum 500 characters.') }}</small>
                            <x-input-error :messages="$errors->get('description')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="icon" :value="__('Icon')" />
                            <input type="file" class="form-control @error('icon') is-invalid @enderror" name="icon" id="icon" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
                            <small class="text-muted">{{ __('Accepted: JPG, PNG, GIF, WebP, SVG (max 2MB)') }}</small>
                            <x-input-error :messages="$errors->get('icon')" class="mt-1" />
                            <div id="icon-preview" class="mt-2" style="display: none;">
                                <img id="preview-img" src="" alt="{{ __('Icon preview') }}" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                            <x-primary-button>{{ __('Create Category') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card fh-adm-table-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">{{ __('Tips') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.875rem;">
                        <li class="mb-2">{{ __('Keep category names short and descriptive.') }}</li>
                        <li class="mb-2">{{ __('Slugs are used in URLs and should be lowercase.') }}</li>
                        <li class="mb-0">{{ __('Icons appear in category listings and detail views.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('icon').addEventListener('change', function(e) {
            const preview = document.getElementById('icon-preview');
            const img = document.getElementById('preview-img');
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    img.src = ev.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(e.target.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
    @endpush
@endsection
