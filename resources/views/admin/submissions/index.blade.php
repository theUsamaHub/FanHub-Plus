@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('User Submissions') }}</h2>
        </div>
    </div>

    <x-admin-stats :items="[
        ['label' => __('Pending'), 'value' => $stats['pending'], 'accent' => 'warning'],
        ['label' => __('Published'), 'value' => $stats['published'], 'accent' => 'success'],
        ['label' => __('Rejected'), 'value' => $stats['rejected'], 'accent' => 'danger'],
        ['label' => __('Total'), 'value' => $stats['total'], 'accent' => 'secondary'],
    ]" />

    <div class="card fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.submissions.index') }}" class="row g-2">
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (['pending_review', 'published', 'rejected', 'draft'] as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card fh-adm-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Submitter') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Submitted') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $item)
                            <tr>
                                <td>{{ $item->submittedBy?->name ?? __('Guest') }}</td>
                                <td class="fw-medium">{{ \Illuminate\Support\Str::limit($item->title, 50) }}</td>
                                <td>{{ $item->category?->name ?? '-' }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->created_at->diffForHumans() }}</td>
                                <td>
                                    @php $statusTone = ['draft' => 'muted', 'pending_review' => 'warning', 'published' => 'success', 'rejected' => 'danger'][$item->status] ?? 'muted'; @endphp
                                    <span class="fh-adm-chip" data-tone="{{ $statusTone }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.submissions.show', $item) }}" class="btn btn-outline-info" title="{{ __('Preview') }}"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.contents.edit', $item) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        @if ($item->status === 'pending_review')
                                            <form action="{{ route('admin.submissions.approve', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Approve and publish this submission?') }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success" title="{{ __('Approve') }}"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form action="{{ route('admin.submissions.reject', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Reject this submission?') }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ __('Reject') }}"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-inbox"></i>
                                        <p>{{ $status === 'pending_review' ? __('All caught up - no fan submissions are waiting for review.') : __('No submissions match this filter.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($submissions->hasPages())
            <div class="card-footer bg-white">{{ $submissions->links() }}</div>
        @endif
    </div>
@endsection
