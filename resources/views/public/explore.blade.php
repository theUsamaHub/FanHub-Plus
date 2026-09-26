@extends('layouts.public')
@php
    $activeFandom = $filters['category'] ?? '';
    $fandom = $activeFandom ? config('fandoms.'.$activeFandom) : null;
    $activeCategory = $categories->firstWhere('slug', $activeFandom);
    $pageTitle = $activeCategory?->name ?? $fandom['name'] ?? (!empty($filters['featured']) ? 'Featured Stories' : (($filters['sort'] ?? '') === 'popular' ? 'Trending Now' : 'Every fandom.'));
    $heroImage = $contents->first()?->artwork_url ?? asset(config('homepage.artwork.'.($activeFandom ?: 'anime'), config('homepage.images.trending')));
@endphp
@section('title', $pageTitle.' | Fan Hub Plus')
@section('content')
<div class="fandom-page fandom-page--{{ $activeFandom ?: 'all' }}">
    <section class="fandom-hero" aria-labelledby="fandom-title">
        <img class="fandom-hero__art" src="{{ $heroImage }}" alt="" fetchpriority="high" data-image-fallback="{{ asset(config('homepage.images.trending')) }}">
        <div class="fandom-hero__content">
            <p class="fandom-eyebrow"><x-site-icon :name="$fandom['icon'] ?? 'compass'" /> FANHUB PLUS / THE COLLECTION</p>
            <h1 id="fandom-title">{{ $pageTitle }}<span>{{ $activeFandom ? 'Your universe.' : 'One home.' }}</span></h1>
            <p class="fandom-hero__intro">{{ $fandom ? implode(' ', $fandom['lines']) : 'Discover the stories, worlds and people worth obsessing over.' }}</p>
            <a class="fandom-action" href="#fandom-stories">Explore stories <x-site-icon name="arrow" /></a>
        </div>
        <div class="fandom-hero__footer"><span>FOR THE FANS. ALWAYS.</span><span>{{ number_format($contents->total()) }} {{ Str::plural('story', $contents->total()) }} to discover ↘</span></div>
    </section>
    <nav class="fandom-switcher" aria-label="Choose a fandom">
        <a href="{{ route('public.explore') }}" @if(!$activeFandom) aria-current="page" @endif>All universes</a>
        @foreach($categories as $category)
            <a href="{{ route('public.explore', ['category' => $category->slug]) }}" @if($activeFandom === $category->slug) aria-current="page" @endif><x-site-icon :name="config('fandoms.'.$category->slug.'.icon', 'compass')" />{{ $category->name }}</a>
        @endforeach
    </nav>
    <section class="fandom-library" id="fandom-stories" aria-labelledby="stories-heading">
        <div class="fandom-section-title"><div><p class="fandom-eyebrow">THE LATEST CHAPTER</p><h2 id="stories-heading">Stay in your <em>element.</em></h2></div><p>Fresh perspectives. Familiar obsessions.</p></div>
        <form action="{{ route('public.explore') }}" method="GET" class="fandom-search" role="search">
            <label>Search stories<input type="search" name="q" maxlength="120" value="{{ $filters['q'] ?? '' }}" placeholder="What are you into?"></label>
            <label>Fandom<select name="category"><option value="">All fandoms</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected($activeFandom === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>
            <label>Sort by<select name="sort"><option value="latest">Latest stories</option><option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>Most popular</option><option value="alphabetical" @selected(($filters['sort'] ?? '') === 'alphabetical')>A to Z</option></select></label>
            <label>Content type<select name="type"><option value="">Everything</option>@foreach(['article', 'image', 'video', 'audio'] as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></label>
            <label>Genre / tag<select name="tag"><option value="">All tags</option>@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected(($filters['tag'] ?? '') == $tag->id)>{{ $tag->name }}</option>@endforeach</select></label>
            <label>Release year<input type="number" name="year" min="1900" max="2200" value="{{ $filters['year'] ?? '' }}" placeholder="Any year"></label>
            @if(!empty($filters['featured']))<input type="hidden" name="featured" value="1">@endif
            <button type="submit" class="fandom-action">Find stories <x-site-icon name="search" /></button>
        </form>
        @auth
        @if($activeCategory)<form class="member-interactions" style="margin:20px 0;padding:14px" method="post" action="{{ route('user.favorites.store', $activeCategory) }}">@csrf @php($following = auth()->user()->favoriteCategories()->where('categories.id', $activeCategory->id)->exists())<input type="hidden" name="saved" value="{{ $following ? 0 : 1 }}"><button class="member-button member-button--quiet" aria-pressed="{{ $following ? 'true' : 'false' }}">{{ $following ? '♥ Following '.$activeCategory->name : '♡ Follow '.$activeCategory->name }}</button> <a href="{{ route('user.favorites') }}">Manage favorite fandoms →</a>@include('user.partials.messages')</form>@endif
        @endauth
        @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
        <div class="fandom-results"><span>{{ $contents->total() }} {{ Str::plural('story', $contents->total()) }} found</span>@if(!empty($filters['q']) || !empty($filters['type']) || !empty($filters['featured']))<a href="{{ route('public.explore', array_filter(['category' => $activeFandom])) }}">Clear filters →</a>@else<span>CURATED FOR YOUR CURIOSITY</span>@endif</div>
        <div class="fandom-story-grid">
            @forelse($contents as $story)
                <x-fandom-story-card :story="$story" />
            @empty
                <div class="fandom-empty"><x-site-icon :name="$fandom['icon'] ?? 'compass'" /><h3>A new chapter is on its way.</h3><p>No stories found yet. Try another search or fandom.</p><a class="fandom-action" href="{{ route('public.explore') }}">Explore all stories <x-site-icon name="arrow" /></a></div>
            @endforelse
        </div>
        @if($contents->hasPages())
            <nav class="fandom-pagination" aria-label="Search results pages">
                @if($contents->previousPageUrl())<a href="{{ $contents->previousPageUrl() }}" rel="prev">← Previous</a>@else<span>← Previous</span>@endif
                <span>Page {{ $contents->currentPage() }} of {{ $contents->lastPage() }}</span>
                @if($contents->nextPageUrl())<a href="{{ $contents->nextPageUrl() }}" rel="next">Next →</a>@else<span>Next →</span>@endif
            </nav>
        @endif
    </section>
</div>
@endsection
