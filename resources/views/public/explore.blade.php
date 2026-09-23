@extends('layouts.public')
@section('title', 'Explore | Fan Hub Plus')
@section('content')
<section class="fh-content-page">
    <h1>{{ !empty($filters['category']) ? config('fandoms.'.$filters['category'].'.name') : (!empty($filters['featured']) ? 'Featured Stories' : (($filters['sort'] ?? '') === 'popular' ? 'Trending Now' : 'Explore')) }}</h1>
    <p>Find stories from every corner of your fandom universe.</p>
    <form action="{{ route('public.explore') }}" method="GET" class="fh-search-form" role="search">
        <label>Search<input type="search" name="q" maxlength="120" value="{{ $filters['q'] ?? '' }}" placeholder="Search stories…"></label>
        <label>Fandom<select name="category"><option value="">All fandoms</option>@foreach(config('fandoms') as $slug => $fandom)<option value="{{ $slug }}" @selected(($filters['category'] ?? '') === $slug)>{{ $fandom['name'] }}</option>@endforeach</select></label>
        <label>Sort by<select name="sort"><option value="latest">Latest</option><option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>Most popular</option></select></label>
        @if(!empty($filters['featured'])) <input type="hidden" name="featured" value="1"> @endif
        <button type="submit" class="fh-button">Explore</button>
    </form>
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    <p>{{ $contents->total() }} {{ Str::plural('story', $contents->total()) }} found</p>
    <div class="fh-results">
        @forelse($contents as $content)
            <article class="fh-result"><span>{{ $content->category?->name }}</span><h2>{{ $content->title }}</h2><p>{{ $content->excerpt }}</p></article>
        @empty
            <p>No stories found yet. Try another search or fandom.</p>
        @endforelse
    </div>
    @if($contents->hasPages())
        <nav class="fh-pagination" aria-label="Search results pages">
            @if($contents->previousPageUrl())<a href="{{ $contents->previousPageUrl() }}">← Previous</a>@else<span></span>@endif
            <span>Page {{ $contents->currentPage() }} of {{ $contents->lastPage() }}</span>
            @if($contents->nextPageUrl())<a href="{{ $contents->nextPageUrl() }}">Next →</a>@else<span></span>@endif
        </nav>
    @endif
</section>
@endsection
