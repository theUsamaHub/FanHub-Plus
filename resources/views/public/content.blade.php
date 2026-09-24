@extends('layouts.public')
@section('title', $content->title.' | Fan Hub Plus')
@section('content')
<article class="fh-content-page">
    <a href="{{ route('public.explore') }}">← Explore stories</a>
    <p>{{ $content->category?->name }} · {{ ucfirst($content->type) }}</p>
    <h1>{{ $content->title }}</h1>
    <p>@if($content->submittedBy)By {{ $content->submittedBy->name }} · @endif{{ $content->reading_minutes }} min read @if($content->published_at)· {{ $content->published_at->format('M d, Y') }}@endif</p>
    <img class="fh-detail-image" src="{{ $content->artwork_url }}" width="576" height="324" alt="">
    @if($content->release_date)<p>Release date: <time datetime="{{ $content->release_date->format('Y-m-d') }}">{{ $content->release_date->format('F j, Y') }}</time></p>@endif
    <p>{{ $content->excerpt }}</p>
    <div class="fh-detail-body">{{ trim(strip_tags($content->body ?? '')) }}</div>
</article>
@endsection
