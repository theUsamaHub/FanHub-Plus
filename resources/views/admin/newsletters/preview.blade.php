@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Preview: ') }}{{ $newsletter->subject }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Preview how this email will look for a subscriber.') }}</p>
            </div>
            <a href="{{ route('admin.newsletters.show', $newsletter) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Email Preview') }}</h6>
                </div>
                <div class="card-body">
                    @if ($sampleRecipient)
                        <div class="alert alert-info mb-4">
                            <strong>{{ __('Previewing as:') }}</strong> {{ $sampleRecipient->email }}
                            @if ($sampleRecipient->name) ({{ $sampleRecipient->name }}) @endif
                        </div>
                    @endif

                    <div class="fh-adm-email-preview" style="border: 1px solid var(--fh-adm-line); border-radius: 8px; overflow: hidden; background: #fff;">
                        @php
                            $body = $newsletter->body;
                            if ($sampleRecipient) {
                                $body = str_replace('{name}', $sampleRecipient->name ?? 'Subscriber', $body);
                                $body = str_replace('{email}', $sampleRecipient->email, $body);
                                $body = str_replace('{unsubscribe_url}', route('unsubscribe', ['token' => $sampleRecipient->unsubscribe_token]), $body);
                            } else {
                                $body = str_replace('{name}', 'Subscriber', $body);
                                $body = str_replace('{email}', 'subscriber@example.com', $body);
                                $body = str_replace('{unsubscribe_url}', '#', $body);
                            }
                        @endphp
                        {!! $body !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection