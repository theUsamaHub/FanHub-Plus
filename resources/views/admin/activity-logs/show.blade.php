@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Activity Detail') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">
                    {{ $log->created_at->format('l, j F Y \a\t g:i A') }} · {{ $log->created_at->diffForHumans() }}
                </p>
            </div>
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Log') }}
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            {{-- The headline --}}
            <div class="card mb-3 fh-adm-detail-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="fh-adm-chip" data-tone="{{ $log->event_tone }}" style="padding:7px 13px;font-size:.8rem;">
                            <i class="bi {{ $log->event_icon }}"></i>{{ $log->event_label }}
                        </span>
                        <div class="flex-grow-1">
                            <div class="fs-5 fw-semibold">{{ $log->description }}</div>
                            <div class="text-muted mt-1" style="font-size:.82rem;">
                                {{ trans_choice('{1} :count field recorded|[2,*] :count fields recorded', $log->change_count, ['count' => $log->change_count]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Field by field diff --}}
            <div class="card fh-adm-detail-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Field Changes') }}</h6>
                </div>
                <div class="card-body p-0">
                    @php $visible = collect($log->changes)->reject(fn ($c) => $c['type'] === 'unchanged'); @endphp

                    @if ($visible->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox d-block mb-2 opacity-50" style="font-size:1.8rem;"></i>
                            <p class="mb-0 text-muted" style="font-size:.85rem;">
                                {{ $log->event === 'login' ? __('This entry records a sign-in; no field values were captured.') : __('No field-level values were captured for this entry.') }}
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0 fh-adm-detail-table">
                                <thead>
                                    <tr>
                                        <th style="width:22%;">{{ __('Field') }}</th>
                                        <th style="width:39%;">{{ __('Before') }}</th>
                                        <th style="width:39%;">{{ __('After') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($visible as $change)
                                        <tr>
                                            <td class="fw-semibold" style="font-size:.83rem;">
                                                {{ $change['label'] }}
                                                <div class="text-muted" style="font-size:.68rem; font-weight:400;">{{ $change['field'] }}</div>
                                            </td>
                                            <td class="fh-adm-value" data-side="old">
                                                @if ($change['type'] === 'added')
                                                    <span class="text-muted fst-italic">{{ __('Not set') }}</span>
                                                @else
                                                    {{ $logger->formatValue($change['old'], 400) }}
                                                @endif
                                            </td>
                                            <td class="fh-adm-value" data-side="new">
                                                @if ($change['type'] === 'removed')
                                                    <span class="text-muted fst-italic">{{ __('Removed') }}</span>
                                                @else
                                                    {{ $logger->formatValue($change['new'], 400) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3 fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Context') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table mb-0 fh-adm-detail-table">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width:110px;">{{ __('Actor') }}</td>
                                <td>
                                    @if ($log->user)
                                        <a href="{{ route('admin.users.show', $log->user) }}">{{ $log->user->name }}</a>
                                        <div class="text-muted" style="font-size:.72rem;">{{ $log->user->email }}</div>
                                    @else
                                        <span class="fh-adm-chip" data-tone="muted">{{ __('System') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Record type') }}</td>
                                <td>{{ $log->model_label }} <code style="font-size:.7rem;">{{ class_basename($log->auditable_type) }}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Record') }}</td>
                                <td>
                                    @if ($log->subject)
                                        {{ $log->subject }}
                                    @else
                                        <span class="text-muted">#{{ $log->auditable_id }}</span>
                                    @endif
                                    @if ($log->target_url)
                                        <a href="{{ $log->target_url }}" class="d-block" style="font-size:.75rem;">{{ __('Open record') }} <i class="bi bi-box-arrow-up-right"></i></a>
                                    @elseif (! $log->target_exists && ! in_array($log->event, ['deleted', 'force_deleted'], true))
                                        <span class="text-muted d-block" style="font-size:.72rem;">{{ __('Record no longer exists') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('IP address') }}</td>
                                <td><code style="font-size:.75rem;">{{ $log->ip_address ?? '—' }}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Browser') }}</td>
                                <td style="font-size:.75rem; word-break:break-word;">{{ $log->user_agent ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Entry ID') }}</td>
                                <td><code style="font-size:.75rem;">#{{ $log->id }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Raw Snapshot') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2" style="font-size:.72rem;">{{ __('Exactly what was stored, for troubleshooting.') }}</p>
                    <div class="fh-adm-raw-label">{{ __('Before') }}</div>
                    <pre class="fh-adm-raw mb-3">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'null' }}</pre>
                    <div class="fh-adm-raw-label">{{ __('After') }}</div>
                    <pre class="fh-adm-raw">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'null' }}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
