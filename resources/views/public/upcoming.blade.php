@extends('layouts.public')
@section('title', 'Upcoming Releases | Fan Hub Plus')
@section('content')
<section class="fh-content-page">
    <h1>Upcoming Releases</h1>
    <form method="GET" class="fh-search-form"><label>Fandom<select name="category"><option value="all">All</option>@foreach($filters as $filter)<option value="{{ $filter->slug }}" @selected($activeFilter === $filter->slug)>{{ $filter->name }}</option>@endforeach<option value="merchandise" @selected($activeFilter === 'merchandise')>Merchandise</option></select></label><button class="fh-button" type="submit">Filter</button></form>
    <div class="fh-results">
        @forelse($releases as $release)
            <article class="fh-result"><p>{{ $release['category'] }} · {{ $release['date']?->format('M d, Y') ?? 'Date TBA' }}</p><h2><a href="{{ $release['url'] }}">{{ $release['title'] }}</a></h2><p>{{ ucwords($release['label']) }}</p><a href="{{ $release['url'] }}">Explore release →</a></article>
        @empty<p>No upcoming releases announced yet.</p>@endforelse
    </div>
    @if($releases->hasPages())
        <nav class="fh-pagination" aria-label="Upcoming release pages">
            @if($releases->previousPageUrl())<a href="{{ $releases->previousPageUrl() }}">← Previous</a>@else<span></span>@endif
            <span>Page {{ $releases->currentPage() }} of {{ $releases->lastPage() }}</span>
            @if($releases->nextPageUrl())<a href="{{ $releases->nextPageUrl() }}">Next →</a>@else<span></span>@endif
        </nav>
    @endif
</section>
@endsection
