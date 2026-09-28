@extends('layouts.public')
@section('title', 'Fan Content | Fan Hub Plus')
@section('content')
<div class="fan-list">
    <nav class="fan-list__breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Fan Content</span></nav>
    <header class="fan-list__heading"><div><p class="fan-list__eyebrow">From fans, for fans</p><h1>Fan Content</h1><p>A place for your stories, your art, and your take on the worlds you love.</p></div><a class="fh-button" href="{{ route('user.submissions.create') }}">Share your creation <i class="bi bi-plus-lg" aria-hidden="true"></i></a></header>
    <form class="fan-list__filters" action="{{ route('public.fan-content.index') }}" method="get" role="search">
        <label>Search creations<input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Find a story or creation"></label>
        <label>Fandom<select name="category"><option value="">All fandoms</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>
        <label>Content type<select name="type"><option value="">All types</option>@foreach(['article' => 'Stories', 'image' => 'Artwork', 'video' => 'Videos', 'audio' => 'Audio'] as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
        <button class="fh-button" type="submit">Find creations</button>
    </form>
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    <div class="fan-list__results"><span>{{ number_format($contents->total()) }} {{ Str::plural('creation', $contents->total()) }}</span>@if(collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty())<a href="{{ route('public.fan-content.index') }}">Clear filters</a>@endif</div>
    <div class="fan-list__grid">@forelse($contents as $content)<x-fan-content-card :content="$content" />@empty<div class="fan-list__empty"><i class="bi bi-palette" aria-hidden="true"></i><h2>No creations found</h2><p>Try another search or come back for new fan creations.</p><a href="{{ route('public.fan-content.index') }}">Browse all fan content</a></div>@endforelse</div>
    @if($contents->hasPages())<nav class="fan-list__pagination" aria-label="Fan content pages">@if($contents->previousPageUrl())<a href="{{ $contents->previousPageUrl() }}" rel="prev">← Previous</a>@else<span>Previous</span>@endif<span>Page {{ $contents->currentPage() }} of {{ $contents->lastPage() }}</span>@if($contents->nextPageUrl())<a href="{{ $contents->nextPageUrl() }}" rel="next">Next →</a>@else<span>Next</span>@endif</nav>@endif
</div>
@include('public.fan-content.modal')
@endsection
