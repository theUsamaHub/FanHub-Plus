<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Basic') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="form-control" :value="old('title', $event?->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $event?->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <div class="mb-0">
                        <x-input-label for="category_id" :value="__('Category (optional)')" />
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $event?->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Location') }}</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="form-control" :value="old('city', $event?->city)" required />
                            <x-input-error :messages="$errors->get('city')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="venue" :value="__('Venue')" />
                            <x-text-input id="venue" name="venue" type="text" class="form-control" :value="old('venue', $event?->venue)" />
                            <x-input-error :messages="$errors->get('venue')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" name="address" type="text" class="form-control" :value="old('address', $event?->address)" />
                            <x-input-error :messages="$errors->get('address')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-0">
                            <x-input-label for="latitude" :value="__('Latitude')" />
                            <x-text-input id="latitude" name="latitude" type="number" step="0.00000001" min="-90" max="90" class="form-control" :value="old('latitude', $event?->latitude)" />
                            <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-0">
                            <x-input-label for="longitude" :value="__('Longitude')" />
                            <x-text-input id="longitude" name="longitude" type="number" step="0.00000001" min="-180" max="180" class="form-control" :value="old('longitude', $event?->longitude)" />
                            <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Schedule') }}</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label for="start_at" :value="__('Start')" />
                            <x-text-input id="start_at" name="start_at" type="datetime-local" class="form-control" :value="old('start_at', $event?->start_at?->format('Y-m-d\TH:i'))" required />
                            <x-input-error :messages="$errors->get('start_at')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-0">
                            <x-input-label for="end_at" :value="__('End (optional)')" />
                            <x-text-input id="end_at" name="end_at" type="datetime-local" class="form-control" :value="old('end_at', $event?->end_at?->format('Y-m-d\TH:i'))" />
                            <x-input-error :messages="$errors->get('end_at')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Publishing') }}</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (['draft', 'published', 'cancelled'] as $statusOption)
                                    <option value="{{ $statusOption }}" @selected(old('status', $event?->status ?? 'draft') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="ticket_url" :value="__('Ticket URL')" />
                            <x-text-input id="ticket_url" name="ticket_url" type="url" class="form-control" :value="old('ticket_url', $event?->ticket_url)" />
                            <x-input-error :messages="$errors->get('ticket_url')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="cover_media_id" :value="__('Cover media')" />
                            <select name="cover_media_id" id="cover_media_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($images as $image)
                                    <option value="{{ $image->id }}" @selected((int) old('cover_media_id', $event?->cover_media_id) === $image->id)>{{ $image->original_filename }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('cover_media_id')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <x-primary-button>{{ $event ? __('Update Event') : __('Create Event') }}</x-primary-button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card fh-adm-detail-card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Validation rules') }}</h6></div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: 0.875rem;">
                    <li class="mb-2">{{ __('Start date and time is required.') }}</li>
                    <li class="mb-2">{{ __('End cannot be before start.') }}</li>
                    <li class="mb-2">{{ __('Latitude and longitude must be valid coordinates.') }}</li>
                    <li class="mb-0">{{ __('Cancelled events stay visible to admins.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
