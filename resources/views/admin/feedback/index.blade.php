@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Feedback') }}</h2>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col"><div class="card border-start border-warning border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Open') }}</div><div class="fw-semibold">{{ $stats['open'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-info border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('In review') }}</div><div class="fw-semibold">{{ $stats['in_review'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-success border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Resolved') }}</div><div class="fw-semibold">{{ $stats['resolved'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-secondary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Closed') }}</div><div class="fw-semibold">{{ $stats['closed'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-primary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Total') }}</div><div class="fw-semibold">{{ $stats['total'] }}</div></div></div></div>
    </div>

    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.feedback.index') }}" class="row g-2">
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach (['bug', 'suggestion', 'query'] as $typeOption)
                            <option value="{{ $typeOption }}" @selected(request('type') === $typeOption)>{{ ucfirst($typeOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (['open', 'in_review', 'resolved', 'closed'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucwords(str_replace('_', ' ', $statusOption)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="source">
                        <option value="">{{ __('User / Guest') }}</option>
                        <option value="user" @selected(request('source') === 'user')>{{ __('Registered') }}</option>
                        <option value="guest" @selected(request('source') === 'guest')>{{ __('Guest') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}" title="{{ __('From') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}" title="{{ __('To') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.feedback.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card fh-adm-form-card">
        <div class="card-body p-0">
            <div class="table-responsive fh-adm-table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('From') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($feedback as $item)
                            <tr>
                                <td>{{ $item->user?->name ?? __('Guest') }}</td>
                                <td>{{ ucfirst($item->type) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item->message, 50) }}</td>
                                <td>
                                    @php $statusClass = ['open' => 'warning', 'in_review' => 'info', 'resolved' => 'success', 'closed' => 'secondary'][$item->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                                </td>
                                <td>{{ $item->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.feedback.show', $item) }}" class="btn btn-outline-info btn-sm" title="{{ __('Open') }}"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-megaphone"></i>
                                        <p>{{ __('No feedback found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($feedback->hasPages())
            <div class="card-footer bg-white">{{ $feedback->links() }}</div>
        @endif
    </div>
@endsection
