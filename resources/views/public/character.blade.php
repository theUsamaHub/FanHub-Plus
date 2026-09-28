@extends('layouts.public')
@section('title', $character->name.' | Fan Hub Plus')
@section('content')
<article class="character-detail">
    <a class="character-detail__back" href="{{ route('home') }}#characters">← Character spotlight</a>
    <header class="character-detail__hero">
        <div class="character-detail__portrait">
            <img src="{{ $character->artwork_url }}" data-image-fallback="{{ asset(config('homepage.images.character')) }}" width="474" height="632" alt="{{ $character->name }}" fetchpriority="high">
        </div>
        <div class="character-detail__intro">
            <p class="character-detail__category">{{ $character->category?->name ?? 'Fandom' }} <span>/ Character spotlight</span></p>
            <h1>{{ $character->name }}</h1>
            <div class="character-detail__bio">{{ trim(strip_tags($character->bio ?? '')) ?: 'There’s more to discover about this character. Explore their stories, collection and community below.' }}</div>
            <a class="fh-button" href="#character-content">Explore their world <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
            <a class="character-detail__community-link" href="#community">Join the conversation</a>
        </div>
    </header>
    <nav class="character-detail__nav" aria-label="Character sections">
        <a href="#character-content">Related content <span>{{ $stories->total() }}</span></a>
        <a href="#character-merchandise">Merchandise <span>{{ $merchandise->total() }}</span></a>
        <a href="#character-events">Events <span>{{ $events->total() }}</span></a>
        <a href="#community">Community</a>
    </nav>
    <section class="character-detail__section" id="character-content" aria-labelledby="character-content-title">
        <div class="character-detail__heading"><div><h2 id="character-content-title">Inside their world</h2><p>Stories, videos and more featuring {{ $character->name }}.</p></div></div>
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
        <div class="character-detail__heading"><div><h2 id="character-merchandise-title">For your collection</h2><p>Merchandise inspired by {{ $character->name }}.</p></div></div>
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
    <section class="character-detail__section" id="character-events" aria-labelledby="character-events-title">
        <div class="character-detail__heading"><div><h2 id="character-events-title">Meet beyond the screen</h2><p>Upcoming and ongoing events connected to their stories.</p></div><a href="{{ route('events.index') }}">All events <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="character-detail__events">
            @forelse($events as $event)
                <a class="character-detail__event" href="{{ route('events.show', $event->slug) }}">
                    <img src="{{ $event->artwork_url }}" data-image-fallback="{{ $event->fallback_artwork }}" width="240" height="180" alt="" loading="lazy">
                    <time class="character-detail__date" datetime="{{ $event->start_at->toIso8601String() }}"><span>{{ $event->start_at->format('M') }}</span><strong>{{ $event->start_at->format('d') }}</strong><span>{{ $event->start_at->format('Y') }}</span></time>
                    <div><span class="character-detail__meta">{{ $event->display_status }}</span><h3>{{ $event->title }}</h3><p>{{ implode(' • ', array_filter([$event->venue, $event->city])) ?: 'Location to be announced' }}</p></div>
                    <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                </a>
            @empty
                <div class="character-detail__empty"><i class="bi bi-calendar-event" aria-hidden="true"></i><h3>The next gathering awaits</h3><p>No upcoming events are linked to this character’s stories yet.</p><a href="{{ route('events.index') }}">Discover all events</a></div>
            @endforelse
        </div>
        {{ $events->links() }}
    </section>
    <x-member-interactions :item="$character" type="character" />
</article>
@endsection
