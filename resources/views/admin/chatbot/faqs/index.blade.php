@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Chatbot FAQs') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.chatbot.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-chat-dots me-1"></i>{{ __('Chatbot Queries') }}
                </a>
                <a href="{{ route('admin.chatbot.faqs.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('Add FAQ') }}
                </a>
            </div>
        </div>
    </div>

    <x-admin-stats :items="[
        ['label' => __('Total FAQs'), 'value' => $stats['total'], 'accent' => 'primary'],
        ['label' => __('Added This Month'), 'value' => $stats['this_month'], 'accent' => 'success'],
        ['label' => __('Categorized'), 'value' => $stats['categorized'], 'accent' => 'info'],
        ['label' => __('Uncategorized'), 'value' => $stats['uncategorized'], 'accent' => 'secondary'],
    ]" />

    <div class="card fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.chatbot.faqs.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="search" class="form-control" name="search" placeholder="{{ __('Search questions and answers...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category_id">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}" aria-label="{{ __('From date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}" aria-label="{{ __('To date') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary" title="{{ __('Filter') }}"><i class="bi bi-search"></i></button>
                    <a href="{{ route('admin.chatbot.faqs.index') }}" class="btn btn-outline-danger" title="{{ __('Clear') }}"><i class="bi bi-x-lg"></i></a>
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
                            <th>{{ __('Question') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Created By') }}</th>
                            <th>{{ __('Updated') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($faqs as $faq)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.chatbot.faqs.show', $faq) }}" class="fw-medium text-decoration-none">{{ $faq->question }}</a>
                                    <div class="text-muted" style="font-size:0.78rem;">{{ \Illuminate\Support\Str::limit($faq->answer, 90) }}</div>
                                </td>
                                <td>{{ $faq->category?->name ?? __('Uncategorized') }}</td>
                                <td>{{ $faq->createdBy?->name ?? __('System') }}</td>
                                <td>{{ $faq->updated_at?->diffForHumans() }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.chatbot.faqs.show', $faq) }}" class="btn btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.chatbot.faqs.edit', $faq) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.chatbot.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this FAQ?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-question-circle"></i>
                                        <p>{{ __('No FAQs found.') }}</p>
                                        <a href="{{ route('admin.chatbot.faqs.create') }}" class="btn btn-primary btn-sm mt-2">{{ __('Add your first FAQ') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($faqs->hasPages())
            <div class="card-footer">{{ $faqs->links() }}</div>
        @endif
    </div>
@endsection
