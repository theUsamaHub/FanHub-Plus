@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Chatbot Queries') }}</h2>
            <a href="{{ route('admin.chatbot.export') . '?' . http_build_query(request()->only(['search', 'filter', 'from', 'to'])) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i>{{ __('Export CSV') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Total Queries') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Today') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['today'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Unique Users') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['unique_users'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Sessions') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['unique_sessions'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Questions -->
    @if($topQuestions->count())
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-fire me-1"></i>{{ __('Most Asked Questions') }}</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead><tr><th>#</th><th>Question</th><th class="text-end">Count</th></tr></thead>
                <tbody>
                @foreach($topQuestions as $i => $q)
                    <tr><td>{{ $i + 1 }}</td><td>{{ Str::limit($q->message, 100) }}</td><td class="text-end"><span class="badge bg-primary">{{ $q->count }}</span></td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search messages...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="filter">
                        <option value="">{{ __('All') }}</option>
                        <option value="faq" {{ request('filter') === 'faq' ? 'selected' : '' }}>{{ __('FAQ Match') }}</option>
                        <option value="ai" {{ request('filter') === 'ai' ? 'selected' : '' }}{{ request('filter') === 'ai' ? ' selected' : '' }}>{{ __('AI Response') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-search me-1"></i>{{ __('Filter') }}</button>
                    <a href="{{ route('admin.chatbot.index') }}" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Message</th>
                            <th>Response</th>
                            <th>User</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($queries as $q)
                        <tr>
                            <td class="text-muted">{{ $q->id }}</td>
                            <td style="max-width:200px;">{{ Str::limit($q->message, 80) }}</td>
                            <td style="max-width:300px;">{{ Str::limit($q->response ?? '-', 120) }}</td>
                            <td>{{ $q->user?->email ?? 'Guest' }}</td>
                            <td class="text-muted" style="font-size:0.8rem;">{{ $q->created_at?->diffForHumans() }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.chatbot.destroy', $q) }}" onsubmit="return confirm('Delete this query?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No chatbot queries found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($queries->hasPages())
        <div class="card-footer">{{ $queries->links() }}</div>
        @endif
    </div>
@endsection
