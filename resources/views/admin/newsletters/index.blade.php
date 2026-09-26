@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Newsletters') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Manage and send email newsletters to subscribers.') }}</p>
            </div>
            <a href="{{ route('admin.newsletters.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Create Newsletter') }}
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-start border-secondary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Total') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Sent') }}</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['sent'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Drafts') }}</div>
                    <div class="fs-4 fw-bold text-primary">{{ $stats['draft'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Sending') }}</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['sending'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>{{ __('Sending') }}</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>{{ __('Sent') }}</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>{{ __('Custom') }}</option>
                        <option value="content" {{ request('type') === 'content' ? 'selected' : '' }}>{{ __('Content') }}</option>
                        <option value="event" {{ request('type') === 'event' ? 'selected' : '' }}>{{ __('Event') }}</option>
                        <option value="character" {{ request('type') === 'character' ? 'selected' : '' }}>{{ __('Character') }}</option>
                        <option value="merchandise" {{ request('type') === 'merchandise' ? 'selected' : '' }}>{{ __('Merchandise') }}</option>
                        <option value="category" {{ request('type') === 'category' ? 'selected' : '' }}>{{ __('Category') }}</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-search me-1"></i>{{ __('Filter') }}</button>
                    <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Newsletters Table --}}
    <div class="card fh-adm-form-card">
        <div class="card-body p-0">
            <div class="table-responsive fh-adm-table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th class="text-center">{{ __('Recipients') }}</th>
                            <th class="text-center">{{ __('Sent') }}</th>
                            <th class="text-center">{{ __('Failed') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Sent At') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($newsletters as $newsletter)
                            <tr>
                                <td class="fw-medium">{{ \Illuminate\Support\Str::limit($newsletter->subject, 50) }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">{{ $newsletter->type_label }}</span>
                                </td>
                                <td>
                                    @if ($newsletter->type !== 'custom' && $newsletter->reference_id)
                                        @php
                                            $ref = match($newsletter->type) {
                                                'content' => \App\Models\Content::find($newsletter->reference_id),
                                                'event' => \App\Models\Event::find($newsletter->reference_id),
                                                'character' => \App\Models\CharacterProfile::find($newsletter->reference_id),
                                                'merchandise' => \App\Models\MerchandiseItem::find($newsletter->reference_id),
                                                'category' => \App\Models\Category::find($newsletter->reference_id),
                                                default => null,
                                            };
                                        @endphp
                                        @if ($ref)
                                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($ref->title ?? $ref->name ?? '', 30) }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold">{{ $newsletter->recipient_count }}</td>
                                <td class="text-center fw-semibold text-success">{{ $newsletter->sent_count }}</td>
                                <td class="text-center fw-semibold text-danger">{{ $newsletter->failed_count }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'sending' => 'warning',
                                            'sent' => 'success',
                                            'failed' => 'danger',
                                        ];
                                        $color = $statusColors[$newsletter->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucfirst($newsletter->status) }}</span>
                                </td>
                                <td class="text-muted">{{ $newsletter->sent_at?->diffForHumans() ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.newsletters.show', $newsletter) }}" class="btn btn-outline-secondary" title="{{ __('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.newsletters.preview', $newsletter) }}" class="btn btn-outline-info" title="{{ __('Preview') }}">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @if ($newsletter->status === 'draft')
                                            <form action="{{ route('admin.newsletters.send', $newsletter) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Send this newsletter to all recipients?') }}')">
                                                @csrf
                                                <button class="btn btn-success" title="{{ __('Send') ?>">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">{{ __('No newsletters found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($newsletters->hasPages())
                <div class="card-footer bg-white">{{ $newsletters->links() }}</div>
            @endif
        </div>
    </div>
@endsection