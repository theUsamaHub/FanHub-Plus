@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Subscribers') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Manage newsletter subscribers and their preferences.') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.subscribers.export') . '?' . http_build_query(request()->only(['search', 'filter', 'from', 'to'])) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i>{{ __('Export CSV') }}
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Total') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Active') }}</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-secondary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Unsubscribed') }}</div>
                    <div class="fs-4 fw-bold text-secondary">{{ $stats['unsubscribed'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Bounced') }}</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['bounced'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search email or name...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="filter">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('filter') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="unsubscribed" {{ request('filter') === 'unsubscribed' ? 'selected' : '' }}>{{ __('Unsubscribed') }}</option>
                        <option value="bounced" {{ request('filter') === 'bounced' ? 'selected' : '' }}>{{ __('Bounced') }}</option>
                        <option value="complained" {{ request('filter') === 'complained' ? 'selected' : '' }}>{{ __('Complained') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}" placeholder="{{ __('From date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}" placeholder="{{ __('To date') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-search me-1"></i>{{ __('Filter') }}</button>
                    <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Actions Form --}}
    <form action="{{ route('admin.subscribers.bulk') }}" method="POST" id="bulkActionForm">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput">
        <div class="card fh-adm-form-card">
            <div class="card-body p-0">
                <div class="table-responsive fh-adm-table-scroll">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Categories') }}</th>
                                <th>{{ __('Subscribed') }}</th>
                                <th>{{ __('IP') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subscribers as $subscriber)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="subscriber_ids[]" value="{{ $subscriber->id }}" class="form-check-input subscriber-checkbox">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.subscribers.show', $subscriber) }}" class="text-decoration-none">
                                            <code>{{ $subscriber->email }}</code>
                                        </a>
                                    </td>
                                    <td>{{ $subscriber->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'active' => 'success',
                                                'unsubscribed' => 'secondary',
                                                'bounced' => 'warning',
                                                'complained' => 'danger',
                                            ];
                                            $color = $statusColors[$subscriber->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">
                                            {{ ucfirst($subscriber->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $prefs = $subscriber->getPreferences();
                                            $catIds = $prefs['categories'] ?? [];
                                        @endphp
                                        @if (!empty($catIds))
                                            @foreach (array_slice($catIds, 0, 3) as $catId)
                                                @php $cat = \App\Models\Category::find($catId); @endphp
                                                @if ($cat)
                                                    <span class="badge bg-primary-subtle text-primary me-1" style="font-size:.65rem;">{{ \Illuminate\Support\Str::limit($cat->name, 15) }}</span>
                                                @endif
                                            @endforeach
                                            @if (count($catIds) > 3)
                                                <span class="text-muted" style="font-size:.7rem;">+{{ count($catIds) - 3 }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $subscriber->subscribed_at?->diffForHumans() ?? '-' }}</td>
                                    <td><code>{{ $subscriber->ip_address ?? '-' }}</code></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.subscribers.show', $subscriber) }}" class="btn btn-outline-secondary" title="{{ __('View') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.subscribers.edit', $subscriber) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.subscribers.destroy', $subscriber) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this subscriber?') }}')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No subscribers found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: auto;" id="bulkActionSelect">
                            <option value="">{{ __('Bulk Actions') }}</option>
                            <option value="activate">{{ __('Activate') }}</option>
                            <option value="unsubscribe">{{ __('Unsubscribe') }}</option>
                            <option value="bounce">{{ __('Mark Bounced') }}</option>
                            <option value="complain">{{ __('Mark Complained') }}</option>
                            <option value="delete">{{ __('Delete') }}</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="applyBulkAction">
                            <i class="bi bi-check me-1"></i>{{ __('Apply') }}
                        </button>
                    </div>
                    @if ($subscribers->hasPages())
                        {{ $subscribers->links() }}
                    @endif
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.subscriber-checkbox');
        const bulkActionSelect = document.getElementById('bulkActionSelect');
        const applyBulkAction = document.getElementById('applyBulkAction');
        const bulkActionInput = document.getElementById('bulkActionInput');
        const bulkForm = document.getElementById('bulkActionForm');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                selectAll.checked = [...checkboxes].every(c => c.checked);
            });
        });

        applyBulkAction.addEventListener('click', function() {
            const action = bulkActionSelect.value;
            const checked = document.querySelectorAll('.subscriber-checkbox:checked');
            
            if (!action) {
                alert('{{ __('Please select an action.') }}');
                return;
            }
            if (checked.length === 0) {
                alert('{{ __('Please select at least one subscriber.') }}');
                return;
            }
            if (action === 'delete' && !confirm('{{ __('Delete selected subscribers?') }}')) {
                return;
            }

            bulkActionInput.value = action;
            bulkForm.submit();
        });
    });
</script>
@endpush