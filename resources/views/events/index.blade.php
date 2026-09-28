@extends('layouts.public')
@section('title', 'Events | Fan Hub Plus')
@section('main-class', 'events-main')
@push('styles')
    @vite('resources/js/modules/events-page.js')
@endpush
@section('content')
<div class="events-page" data-events-page>
        {{-- Previous hero artwork: images/hero/photo-1667419674923-9eef9f86c628.avif. Kept disabled for the plain theme background. --}}
        <section class="events-intro" aria-labelledby="events-title" data-events-intro>
            <div class="events-intro__top"><span>FANHUB PLUS / IN REAL LIFE</span><span>EVERY FANDOM. ONE PLACE.</span></div>
            <div class="events-intro__center">
                <p class="events-kicker">YOUR NEXT GREAT MEMORY</p>
                <h1 id="events-title" data-events-title>EVENTS<span aria-hidden="true">.</span></h1>
            </div>
            <div class="events-intro__bottom"><p>Conventions. Meetups. Moments that matter.</p><a href="#{{ $featured->isNotEmpty() ? 'featured-events' : 'explore-events' }}">Scroll to discover <span aria-hidden="true">↓</span></a></div>
        </section>

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

    <section class="events-explore events-container" aria-labelledby="explore-title">
        @include('events.partials.nearby-controls')
        <div class="events-section-heading" id="explore-events" data-event-reveal><div><p class="events-kicker">MAKE ROOM IN YOUR CALENDAR</p><h2 id="explore-title">Explore all <em>events.</em></h2></div><p>Find your people.<br>Make it a date.</p></div>
        @include('events.partials.filters')
        <div data-event-results>@include('events.partials.results')</div>
    </section>
</div>
@endsection
