@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Edit FAQ') }}</h2>
            <a href="{{ route('admin.chatbot.faqs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    @include('admin.chatbot.faqs.partials.form', [
        'action' => route('admin.chatbot.faqs.update', $faq),
        'method' => 'PUT',
        'faq' => $faq,
    ])
@endsection
