@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ $character->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.characters.edit', $character) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Edit') }}</a>
                <a href="{{ route('admin.characters.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width:180px;">{{ __('Image') }}</td>
                                <td>
                                    @if ($character->imageMedia)
                                        <img src="{{ $character->imageMedia->url }}" alt="{{ $character->name }}" class="img-thumbnail" style="max-height: 120px;">
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Name') }}</td><td>{{ $character->name }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Slug') }}</td><td><code>{{ $character->slug }}</code></td></tr>
                            <tr><td class="fw-semibold">{{ __('Category') }}</td><td>{{ $character->category?->name ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Bio') }}</td><td style="white-space: pre-wrap;">{{ $character->bio ?: '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">{{ __('Related content') }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($character->contents as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->type }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.characters.contents.detach', [$character, $item]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Remove this related content?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('Remove') }}"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">{{ __('No related content attached.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Attach content') }}</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.characters.contents.attach', $character) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="content_ids[]" class="form-select" multiple size="8" required>
                                @foreach ($availableContents as $item)
                                    @unless ($character->contents->pluck('id')->contains($item->id))
                                        <option value="{{ $item->id }}">{{ $item->title }} ({{ $item->type }})</option>
                                    @endunless
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('content_ids')" class="mt-1" />
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Attach selected') }}</button>
                    </form>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size: 0.875rem;">{{ __('Deleting a character removes its related content links. Content items themselves are kept.') }}</p>
                    <form action="{{ route('admin.characters.destroy', $character) }}" method="POST" onsubmit="return confirm('{{ __('Delete this character?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete Character') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
