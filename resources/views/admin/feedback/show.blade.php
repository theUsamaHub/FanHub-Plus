@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Feedback Detail') }}</h2>
            <a href="{{ route('admin.feedback.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td class="fw-semibold" style="width:180px;">{{ __('From') }}</td><td>{{ $feedback->user?->name ?? __('Guest') }} @if ($feedback->user) &lt;{{ $feedback->user->email }}&gt; @endif</td></tr>
                            <tr><td class="fw-semibold">{{ __('Type') }}</td><td>{{ ucfirst($feedback->type) }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Message') }}</td><td style="white-space: pre-wrap;">{{ $feedback->message }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Status') }}</td>
                                <td>
                                    @php $statusClass = ['open' => 'warning', 'in_review' => 'info', 'resolved' => 'success', 'closed' => 'secondary'][$feedback->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $feedback->status)) }}</span>
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Date') }}</td><td>{{ $feedback->created_at->format('M d, Y H:i') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Update status') }}</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.feedback.status', $feedback) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                @foreach (['open', 'in_review', 'resolved', 'closed'] as $statusOption)
                                    <option value="{{ $statusOption }}" @selected($feedback->status === $statusOption)>{{ ucwords(str_replace('_', ' ', $statusOption)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Save status') }}</button>
                    </form>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.feedback.destroy', $feedback) }}" method="POST" onsubmit="return confirm('{{ __('Delete this feedback?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete Feedback') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
