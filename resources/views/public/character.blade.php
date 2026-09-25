@extends('layouts.public')
@section('title', $character->name.' | Fan Hub Plus')
@section('content')
<article class="fh-content-page">
    <a href="{{ route('home') }}#characters">← Character spotlight</a>
    <p>{{ $character->category?->name ?? 'Fandom' }}</p>
    <h1>{{ $character->name }}</h1>
    <img class="fh-detail-image fh-detail-image--poster" src="{{ $character->artwork_url }}" data-image-fallback="{{ asset(config('homepage.images.character')) }}" width="474" height="843" alt="{{ $character->name }}">
    <div class="fh-detail-body">{{ trim(strip_tags($character->bio ?? '')) }}</div>
    @if($stories->isNotEmpty())
        <h2 class="h4 mt-4">Stories featuring {{ $character->name }}</h2>
        <ul class="list-unstyled">
            @foreach($stories as $story)
                <li class="py-2"><a href="{{ route('public.content', $story->slug) }}">{{ $story->title }}</a></li>
            @endforeach
        </ul>
        {{ $stories->links() }}
    @endif
</article>
@endsection
