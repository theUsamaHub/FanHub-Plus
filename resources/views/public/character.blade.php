@extends('layouts.public')
@section('title', $character->name.' | Fan Hub Plus')
@section('content')
<article class="character-detail">
    <nav class="character-detail__back" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">›</span><a href="{{ route('home') }}#characters">Characters</a><span aria-hidden="true">›</span><span aria-current="page">{{ $character->name }}</span></nav>
    <header class="character-detail__hero">
        <div class="character-detail__portrait" data-character-tilt>
            <img src="{{ $character->artwork_url }}" data-image-fallback="{{ asset(config('homepage.images.character')) }}" width="474" height="632" alt="{{ $character->name }}" fetchpriority="high">
        </div>
        <div class="character-detail__intro">
            <h1>{{ $character->name }}</h1>
            <p class="character-detail__category"><i class="bi bi-sparkle" aria-hidden="true"></i> {{ $character->category?->name ?? 'Fandom' }} character</p>
            <div class="character-detail__tags"><span>{{ $character->category?->name ?? 'Fandom' }}</span><span>Character spotlight</span></div>
            <div class="character-detail__bio">{{ trim(strip_tags($character->bio ?? '')) ?: 'There’s more to discover about this character. Explore their stories, collection and community below.' }}</div>
            <div class="character-detail__facts">
                <a href="#character-content"><i class="bi bi-collection" aria-hidden="true"></i><small>Appears in</small><strong>{{ $stories->total() }} {{ Str::plural('story', $stories->total()) }}</strong></a>
                <a href="#character-merchandise"><i class="bi bi-bag" aria-hidden="true"></i><small>Merchandise</small><strong>{{ $merchandise->total() }} {{ Str::plural('item', $merchandise->total()) }}</strong></a>
            </div>
        </div>
    </header>
    <nav class="character-detail__nav" aria-label="Character sections">
        <a href="#character-content">Related content <span>{{ $stories->total() }}</span></a>
        <a href="#character-merchandise">Merchandise <span>{{ $merchandise->total() }}</span></a>
        <a href="#community">Community</a>
    </nav>
    <section class="character-detail__section" id="character-content" aria-labelledby="character-content-title">
        <div class="character-detail__heading"><h2 id="character-content-title"><i class="bi bi-stack" aria-hidden="true"></i>Appears in</h2><span class="character-detail__meta">{{ $stories->total() }} related</span></div>
        <div class="character-detail__grid">
            @forelse($stories as $story)
                <a class="character-detail__card" href="{{ route('public.content', $story->slug) }}">
                    <img src="{{ $story->artwork_url }}" data-image-fallback="{{ asset(config('homepage.images.story')) }}" width="720" height="405" alt="" loading="lazy">
                    <div class="character-detail__card-body"><span class="character-detail__meta">{{ ucfirst($story->type) }} @if($story->published_at) · {{ $story->published_at->format('M j, Y') }} @endif</span><h3>{{ $story->title }}</h3>@if($story->excerpt)<p>{{ Str::limit(strip_tags($story->excerpt), 130) }}</p>@endif<span class="character-detail__card-action">Explore content <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span></div>
                </a>
            @empty
                <div class="character-detail__empty"><i class="bi bi-journal-richtext" aria-hidden="true"></i><h3>A story still unfolding</h3><p>Content featuring {{ $character->name }} will appear here when published.</p><a href="{{ route('public.explore') }}">Explore all content</a></div>
            @endforelse
        </div>
        {{ $stories->links() }}
    </section>
    <section class="character-detail__section" id="character-merchandise" aria-labelledby="character-merchandise-title">
        <div class="character-detail__heading"><h2 id="character-merchandise-title"><i class="bi bi-bag-fill" aria-hidden="true"></i>Merchandise</h2><span class="character-detail__meta">{{ $merchandise->total() }} items</span></div>
        <div class="character-detail__grid character-detail__grid--merch">
            @forelse($merchandise as $item)
                <a class="character-detail__card character-detail__card--merch" href="{{ route('public.merchandise', $item->slug) }}">
                    <div class="character-detail__product-image"><img src="{{ $item->artwork_url }}" data-image-fallback="{{ asset(config('homepage.images.merchandise')) }}" width="480" height="480" alt="" loading="lazy">@if($item->display_tag)<span>{{ $item->display_tag }}</span>@endif</div>
                    <div class="character-detail__card-body"><h3>{{ $item->name }}</h3>@if($item->release_date)<p>Releases {{ $item->release_date->format('M j, Y') }}</p>@endif<span class="character-detail__card-action">View merchandise <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span></div>
                </a>
            @empty
                <div class="character-detail__empty"><i class="bi bi-box-seam" aria-hidden="true"></i><h3>Room for something special</h3><p>No merchandise is linked to this character yet.</p><a href="{{ route('public.section', 'merchandise') }}">Browse all merchandise</a></div>
            @endforelse
        </div>
        {{ $merchandise->links() }}
    </section>
    <x-member-interactions :item="$character" type="character" />
</article>
@endsection
