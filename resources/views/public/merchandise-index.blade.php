@extends('layouts.public')
@section('title', 'Official Merchandise | Fan Hub Plus')
@section('content')
<section class="fh-content-page">
    <p class="merch-eyebrow">FANHUB PLUS</p>
    <h1>Official <span class="fan-gradient-text">Merchandise</span></h1>
    <p>Show your fandom. Wear the story.</p>
    <nav class="merch-filters" aria-label="Merchandise categories">
        @foreach(['all' => ['name' => 'All'], ...config('fandoms')] as $slug => $category)
            <a href="{{ route('public.section', ['section' => 'merchandise', 'category' => $slug]) }}" @if($activeFilter === $slug) aria-current="true" @endif>{{ $category['name'] }}</a>
        @endforeach
    </nav>
    <div class="merch-grid">
        @forelse($items as $item)<x-merchandise-card :item="$item" :saved="in_array($item->id, $savedMerchandise)" />
        @empty<p class="merch-empty">No merchandise in this fandom yet.</p>@endforelse
    </div>
    @if($items->hasPages())
        <nav class="fh-pagination" aria-label="Merchandise pages">
            @if($items->previousPageUrl())<a href="{{ $items->previousPageUrl() }}">← Previous</a>@else<span></span>@endif
            <span>Page {{ $items->currentPage() }} of {{ $items->lastPage() }}</span>
            @if($items->nextPageUrl())<a href="{{ $items->nextPageUrl() }}">Next →</a>@else<span></span>@endif
        </nav>
    @endif
</section>
@endsection
