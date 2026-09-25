@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h2 class="h4 mb-0 fw-semibold">{{ $faq->question }}</h2>
            <div class="d-flex gap-2 flex-shrink-0">
                <a href="{{ route('admin.chatbot.faqs.edit', $faq) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Edit') }}</a>
                <a href="{{ route('admin.chatbot.faqs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-detail-card">
                <div class="card-body">
                    <table class="table mb-0 fh-adm-detail-table">
                        <tbody>
                            <tr>
                                <td class="fw-semibold">{{ __('Question') }}</td>
                                <td>{{ $faq->question }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Answer') }}</td>
                                <td style="white-space: pre-wrap;">{{ $faq->answer }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Category') }}</td>
                                <td>{{ $faq->category?->name ?? __('Uncategorized') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Created By') }}</td>
                                <td>{{ $faq->createdBy?->name ?? __('System') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Created') }}</td>
                                <td>{{ $faq->created_at?->format('M j, Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Last Updated') }}</td>
                                <td>{{ $faq->updated_at?->diffForHumans() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-danger fh-adm-danger-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:0.875rem;">{{ __('Deleting this FAQ removes its exact chatbot answer immediately.') }}</p>
                    <form action="{{ route('admin.chatbot.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('{{ __('Delete this FAQ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete FAQ') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
