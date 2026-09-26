@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Edit Subscriber') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ $subscriber->email }}</p>
            </div>
            <a href="{{ route('admin.subscribers.show', $subscriber) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Status') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subscribers.update', $subscriber) }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" value="{{ $subscriber->email }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" name="status">
                                <option value="active" {{ $subscriber->status === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="unsubscribed" {{ $subscriber->status === 'unsubscribed' ? 'selected' : '' }}>{{ __('Unsubscribed') }}</option>
                                <option value="bounced" {{ $subscriber->status === 'bounced' ? 'selected' : '' }}>{{ __('Bounced') }}</option>
                                <option value="complained" {{ $subscriber->status === 'complained' ? 'selected' : '' }}>{{ __('Complained') }}</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ __('Save Status') }}
                            </button>
                            <a href="{{ route('admin.subscribers.show', $subscriber) }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card fh-adm-form-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Quick Status Change') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="active">
                            <button class="btn btn-success btn-sm" {{ $subscriber->status === 'active' ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle me-1"></i>{{ __('Activate') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="unsubscribed">
                            <button class="btn btn-secondary btn-sm" {{ $subscriber->status === 'unsubscribed' ? 'disabled' : '' }}>
                                <i class="bi bi-x-circle me-1"></i>{{ __('Unsubscribe') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="bounced">
                            <button class="btn btn-warning btn-sm" {{ $subscriber->status === 'bounced' ? 'disabled' : '' }}>
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ __('Mark Bounced') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="complained">
                            <button class="btn btn-danger btn-sm" {{ $subscriber->status === 'complained' ? 'disabled' : '' }}>
                                <i class="bi bi-flag me-1"></i>{{ __('Mark Complained') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Subscriber Stats') }}</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted" style="font-size:.8rem;">{{ __('Total Emails Sent') }}</dt>
                        <dd class="col-sm-7 fw-bold mb-2">{{ $subscriber->email_count ?? 0 }}</dd>

                        <dt class="col-sm-5 text-muted" style="font-size:.8rem;">{{ __('Last Email Sent') }}</dt>
                        <dd class="col-sm-7 mb-2">{{ $subscriber->last_email_sent_at?->diffForHumans() ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted" style="font-size:.8rem;">{{ __('Subscribed Since') }}</dt>
                        <dd class="col-sm-7 mb-2">{{ $subscriber->subscribed_at?->format('M j, Y') ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted" style="font-size:.8rem;">{{ __('IP Address') }}</dt>
                        <dd class="col-sm-7 mb-2"><code>{{ $subscriber->ip_address ?? '—' }}</code></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection