@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ $subscriber->email }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ $subscriber->name ?? __('No name') }} • {{ $subscriber->subscribed_at?->format('M j, Y') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.subscribers.edit', $subscriber) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                </a>
                <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Subscriber Info') }}</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Email') }}</dt>
                        <dd class="col-sm-8 fw-medium mb-3"><code>{{ $subscriber->email }}</code></dd>

                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Name') }}</dt>
                        <dd class="col-sm-8 mb-3">{{ $subscriber->name ?? '<span class="text-muted">—</span>' }}</dd>

                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Status') }}</dt>
                        <dd class="col-sm-8 mb-3">
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'unsubscribed' => 'secondary',
                                    'bounced' => 'warning',
                                    'complained' => 'danger',
                                ];
                                $color = $statusColors[$subscriber->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} fs-6">
                                {{ ucfirst($subscriber->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Subscribed') }}</dt>
                        <dd class="col-sm-8 mb-3">{{ $subscriber->subscribed_at?->format('M j, Y H:i') ?? '—' }}</dd>

                        @if ($subscriber->unsubscribed_at)
                            <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Unsubscribed') }}</dt>
                            <dd class="col-sm-8 mb-3">{{ $subscriber->unsubscribed_at->format('M j, Y H:i') }}</dd>
                        @endif

                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('IP Address') }}</dt>
                        <dd class="col-sm-8 mb-3"><code>{{ $subscriber->ip_address ?? '—' }}</code></dd>

                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Emails Sent') }}</dt>
                        <dd class="col-sm-8 mb-3">{{ $subscriber->email_count ?? 0 }}</dd>

                        <dt class="col-sm-4 text-muted" style="font-size:.8rem;">{{ __('Last Email') }}</dt>
                        <dd class="col-sm-8 mb-3">{{ $subscriber->last_email_sent_at?->diffForHumans() ?? '—' }}</dd>
                    </dl>

                    <hr class="my-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="active">
                            <button class="btn btn-success btn-sm" title="{{ __('Activate') }}" {{ $subscriber->status === 'active' ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle me-1"></i>{{ __('Activate') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="unsubscribed">
                            <button class="btn btn-secondary btn-sm" title="{{ __('Unsubscribe') }}" {{ $subscriber->status === 'unsubscribed' ? 'disabled' : '' }}>
                                <i class="bi bi-x-circle me-1"></i>{{ __('Unsubscribe') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.subscribers.update-status', $subscriber) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="bounced">
                            <button class="btn btn-warning btn-sm" title="{{ __('Mark Bounced') }}" {{ $subscriber->status === 'bounced' ? 'disabled' : '' }}>
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ __('Bounce') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Category Preferences') }}</h6>
                </div>
                <div class="card-body">
                    @if (!empty($selectedCategories))
                        <div class="mb-3">
                            <strong class="text-muted" style="font-size:.8rem;">{{ __('Subscribed to:') }}</strong>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach ($selectedCategories as $catId)
                                    @php $cat = \App\Models\Category::find($catId); @endphp
                                    @if ($cat)
                                        <span class="badge bg-primary-subtle text-primary">{{ $cat->name }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('No category preferences selected. Will receive all newsletters.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card fh-adm-form-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Quick Actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.newsletters.create') }}?categories[]={{ $selectedCategories[0] ?? '' }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-envelope-heart me-1"></i>{{ __('Send Newsletter') }}
                        </a>
                        <a href="{{ route('admin.subscribers.edit', $subscriber) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil me-1"></i>{{ __('Edit Preferences') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection