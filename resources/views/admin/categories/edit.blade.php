@extends('layouts.app')

@section('content')
    <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Edit Category') }}</h2>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <x-input-label for="name" :value="__('Category Name')" />
                            <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name', $category->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="slug" :value="__('Slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="form-control" :value="old('slug', $category->slug)" />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" maxlength="500" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                            <small class="text-muted">{{ __('Maximum 500 characters.') }}</small>
                            <x-input-error :messages="$errors->get('description')" class="mt-1" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="icon" :value="__('Icon')" />
                            @if ($category->icon_url)
                                <div class="mb-2">
                                    <img src="{{ $category->icon_url }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-height: 80px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_icon" value="1" id="remove_icon">
                                        <label class="form-check-label" for="remove_icon" style="font-size: 0.875rem;">{{ __('Remove current icon') }}</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('icon') is-invalid @enderror" name="icon" id="icon" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
                            <small class="text-muted">{{ __('Leave empty to keep current icon.') }}</small>
                            <x-input-error :messages="$errors->get('icon')" class="mt-1" />
                            <div id="icon-preview" class="mt-2" style="display: none;">
                                <img id="preview-img" src="" alt="{{ __('Icon preview') }}" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                            <x-primary-button>{{ __('Update Category') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">{{ __('Category Info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">{{ __('Created') }}</small>
                        <div>{{ $category->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('Last Updated') }}</small>
                        <div>{{ $category->updated_at->format('M d, Y H:i') }}</div>
                    </div>
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
