@extends('layouts.public')
@section('title', 'Events | Fan Hub Plus')
@section('main-class', 'events-main')
@push('styles')
    @vite('resources/js/modules/events-page.js')
@endpush
@section('content')
<div class="events-page" data-events-page>
    @if(!$hasFilters && $events->currentPage() === 1)
        <section class="events-intro" style="--events-hero-image: url('{{ asset('images/hero/photo-1667419674923-9eef9f86c628.avif') }}')" aria-labelledby="events-title" data-events-intro>
            <div class="events-intro__top"><span>FANHUB PLUS / IN REAL LIFE</span><span>EVERY FANDOM. ONE PLACE.</span></div>
            <div class="events-intro__center">
                <p class="events-kicker">YOUR NEXT GREAT MEMORY</p>
                <h1 id="events-title" data-events-title>EVENTS<span aria-hidden="true">.</span></h1>
                <p class="events-intro__description">Beyond the screen.<br>Into your universe.</p>
            </div>
            <div class="events-intro__bottom"><p>Conventions. Meetups. Moments that matter.</p><a href="#{{ $featured->isNotEmpty() ? 'featured-events' : 'explore-events' }}">Scroll to discover <span aria-hidden="true">↓</span></a></div>
        </section>
    @else
        <header class="events-compact-heading events-container"><a href="{{ route('events.index') }}">← All events</a><h1>Find your next <span>fan moment.</span></h1></header>
    @endif

    @if($featured->isNotEmpty())
        <section class="events-featured events-container" id="featured-events" aria-labelledby="featured-title" data-featured-events>
            <div class="events-section-heading"><div><p class="events-kicker">IN THE SPOTLIGHT</p><h2 id="featured-title">Worth being <em>there.</em></h2></div><a class="events-text-link" href="#explore-events">Skip to all events <x-site-icon name="arrow" /></a></div>
            <div class="events-story-stage" data-event-stage>
                <div class="events-story-meta"><span>FEATURED / <span data-story-count>01</span> — {{ str_pad($featured->count(), 2, '0', STR_PAD_LEFT) }}</span><span>SCROLL TO EXPLORE</span></div>
                <div class="events-story-cards">
                    @foreach($featured as $event)
                        @include('events.partials.featured-event', ['position' => $loop->iteration])
                    @endforeach
                </div>
                <div class="events-story-progress" aria-hidden="true"><span data-story-progress></span></div>
            </div>
        </section>
    @endif

    <section class="events-explore events-container" id="explore-events" aria-labelledby="explore-title">
        <div class="events-section-heading" data-event-reveal><div><p class="events-kicker">MAKE ROOM IN YOUR CALENDAR</p><h2 id="explore-title">Explore all <em>events.</em></h2></div><p>Find your people.<br>Make it a date.</p></div>
        @include('events.partials.filters')
        <div class="events-results-bar"><p><strong>{{ number_format($events->total()) }}</strong> {{ $hasFilters ? 'matching' : 'more' }} {{ \Illuminate\Support\Str::plural('event', $events->total()) }}@if($events->total()) <span> / {{ $events->firstItem() }}–{{ $events->lastItem() }}</span>@endif</p><span>Times shown in {{ config('app.timezone') }}</span></div>
        <div class="events-grid" data-event-grid>
            @forelse($events as $event)
                @include('events.partials.event-card')
            @empty
                <div class="events-empty"><x-site-icon name="compass" /><h3>{{ $hasFilters ? 'No events found this time.' : 'More fan moments are on the way.' }}</h3><p>{{ $hasFilters ? 'Try a different date, city or fandom to find your next event.' : 'Check back soon for new conventions, screenings and meetups.' }}</p>@if($hasFilters)<a class="events-button" href="{{ route('events.index') }}#explore-events">Clear filters <x-site-icon name="arrow" /></a>@endif</div>
            @endforelse
        </div>
        @if($events->hasPages())
            <nav class="events-pagination" aria-label="Event pages">
                @if($events->previousPageUrl())<a href="{{ $events->previousPageUrl() }}" rel="prev">← Previous</a>@else<span aria-disabled="true">← Previous</span>@endif
                <span>Page <strong>{{ $events->currentPage() }}</strong> of {{ $events->lastPage() }}</span>
                @if($events->nextPageUrl())<a href="{{ $events->nextPageUrl() }}" rel="next">Next <span aria-hidden="true">→</span></a>@else<span aria-disabled="true">Next →</span>@endif
            </nav>
        @endif
    </section>
</div>
@endsection
