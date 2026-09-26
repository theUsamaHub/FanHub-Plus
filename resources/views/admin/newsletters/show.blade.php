@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ $newsletter->subject }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ $newsletter->type_label }} newsletter • {{ $newsletter->status }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.newsletters.preview', $newsletter) }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-eye-fill me-1"></i>{{ __('Preview') }}
                </a>
                @if ($newsletter->status === 'draft')
                    <form action="{{ route('admin.newsletters.send', $newsletter) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Send this newsletter to all recipients?') }}')">
                        @csrf
                        <button class="btn btn-success btn-sm">
                            <i class="bi bi-send me-1"></i>{{ __('Send') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Email Content') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted" style="font-size:.75rem;">{{ __('Subject') }}</label>
                        <div class="fw-medium fs-5">{{ $newsletter->subject }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted" style="font-size:.75rem;">{{ __('Body') }}</label>
                        <div class="fh-adm-content-preview" style="border: 1px solid var(--fh-adm-line); border-radius: 8px; padding: 20px; background: var(--fh-adm-surface); max-height: 400px; overflow: auto;">
                            {!! $newsletter->body !!}
                        </div>
                    </div>

                    <div class="row g-3 text-muted" style="font-size:.8rem;">
                        <div class="col-md-6">
                            <strong>{{ __('Type:') }}</strong> {{ $newsletter->type_label }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Status:') }}</strong>
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
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Recipients:') }}</strong> {{ $newsletter->recipient_count }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Sent:') }}</strong> <span class="text-success">{{ $newsletter->sent_count }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Failed:') }}</strong> <span class="text-danger">{{ $newsletter->failed_count }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Created by:') }}</strong> {{ $newsletter->sender?->name ?? 'Unknown' }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Created at:') }}</strong> {{ $newsletter->created_at->format('M j, Y H:i') }}
                        </div>
                        @if ($newsletter->sent_at)
                            <div class="col-md-6">
                                <strong>{{ __('Sent at:') }}</strong> {{ $newsletter->sent_at->format('M j, Y H:i') }}
                            </div>
                        @endif
                    </div>

                    @if ($newsletter->reference_id && $newsletter->type !== 'custom')
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-muted" style="font-size:.8rem;">{{ __('Reference Item:') }}</strong>
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
                                <a href="{{ route('admin.' . $newsletter->type . 's.show', $ref) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('View ') . ucfirst($newsletter->type) }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Recipient Filters') }}</h6>
                </div>
                <div class="card-body">
                    @if ($newsletter->recipient_filters)
                        @if (!empty($newsletter->recipient_filters['categories']))
                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size:.75rem;">{{ __('Categories') }}</label>
                                @foreach ($newsletter->recipient_filters['categories'] as $catId)
                                    @php $cat = \App\Models\Category::find($catId); @endphp
                                    @if ($cat)
                                        <span class="badge bg-primary-subtle text-primary me-1 mb-1">{{ $cat->name }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        @if (!empty($newsletter->recipient_filters['status']))
                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size:.75rem;">{{ __('Status Filter') }}</label>
                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($newsletter->recipient_filters['status']) }}</span>
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('No filters applied - will send to all active subscribers.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card fh-adm-form-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Sample Recipients') }}</h6>
                </div>
                <div class="card-body">
                    @if ($recipients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Categories') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recipients as $subscriber)
                                        <tr>
                                            <td><code class="text-truncate d-block" style="max-width: 180px;">{{ $subscriber->email }}</code></td>
                                            <td>{{ $subscriber->name ?? '—' }}</td>
                                            <td>
                                                @php $cats = $subscriber->getPreferences()['categories'] ?? []; @endphp
                                                @foreach (array_slice($cats, 0, 3) as $catId)
                                                    @php $cat = \App\Models\Category::find($catId); @endphp
                                                    @if ($cat)
                                                        <span class="badge bg-primary-subtle text-primary" style="font-size:.65rem;">{{ \Illuminate\Support\Str::limit($cat->name, 12) }}</span>
                                                    @endif
                                                @endforeach
                                                @if (count($cats) > 3)
                                                    <span class="text-muted" style="font-size:.7rem;">+{{ count($cats) - 3 }} more</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mb-0 mt-2" style="font-size:.75rem;">
                            {{ __('Showing first 50 of ') }}{{ $newsletter->recipient_count }} {{ __(' recipients.') }}
                        </p>
                    @else
                        <p class="text-muted mb-0 text-center py-3">{{ __('No matching recipients found.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection