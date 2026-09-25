@extends('layouts.public')
@section('title', $event->title.' | Fan Hub Plus Events')
@push('styles')
    @vite('resources/js/modules/events-page.js')
@endpush
@section('content')
<article class="events-page event-detail events-container" data-event-detail>
    <a class="events-back" href="{{ route('events.index') }}#explore-events">← Explore all events</a>
    <header class="event-detail__heading" data-event-reveal>
        <div class="event-tags"><span>{{ $event->category?->name ?? 'All fandoms' }}</span>@if($event->type_label)<span>{{ $event->type_label }}</span>@endif<span>{{ $event->display_status }}</span></div>
        <h1>{{ $event->title }}</h1>
        @if($event->short_description)<p>{{ $event->short_description }}</p>@endif
    </header>
    <div class="event-detail__hero" data-detail-hero><img src="{{ $event->artwork_url }}" data-event-image data-fallback="{{ $event->fallback_artwork }}" alt="{{ $event->coverMedia?->alt_text ?: $event->title }}" width="1600" height="800" fetchpriority="high"></div>
    <div class="event-detail__layout">
        <div>
            <section class="event-detail__section" data-event-reveal><p class="events-kicker">THE EXPERIENCE</p><h2>What brings us together.</h2><div class="event-detail__description">{{ $event->description ?: 'More details about this event will be announced soon.' }}</div></section>
            @if($event->galleryMedia->isNotEmpty())
                <section class="event-detail__section" data-event-reveal><p class="events-kicker">A CLOSER LOOK</p><h2>From this universe.</h2><div class="event-gallery">@foreach($event->galleryMedia as $photo)<a href="{{ $photo->url }}" target="_blank" rel="noopener" aria-label="Open gallery image {{ $loop->iteration }}"><img src="{{ $photo->url }}" data-event-image data-fallback="{{ $event->fallback_artwork }}" alt="{{ $photo->alt_text ?: $event->title.' — gallery image '.$loop->iteration }}" width="800" height="600" loading="lazy"></a>@endforeach</div></section>
            @endif
            <section class="event-detail__section" data-event-reveal><p class="events-kicker">MEET US HERE</p><h2>{{ $event->venue ?: $event->city }}</h2><p>{{ $event->address }}{{ $event->address ? ', ' : '' }}{{ $event->city }}</p>@if($event->map_url)<a class="events-location-link" href="{{ $event->map_url }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="compass" /><span>Find the venue<br><small>Open in Google Maps ↗</small></span></a>@endif</section>
        </div>
        <aside class="event-detail__practical" aria-label="Event information" data-event-reveal>
            <p class="events-kicker">YOUR EVENT, AT A GLANCE</p>
            <dl class="event-detail__facts">
                <div><dt>Date</dt><dd><time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('l, F j, Y') }}</time>@if($event->end_at && !$event->start_at->isSameDay($event->end_at))<span>Until {{ $event->end_at->format('F j, Y') }}</span>@endif</dd></div>
                <div><dt>Time · {{ config('app.timezone') }}</dt><dd>{{ $event->start_at->format('g:i A') }}@if($event->end_at && $event->start_at->isSameDay($event->end_at)) – {{ $event->end_at->format('g:i A') }}@endif</dd></div>
                <div><dt>Location</dt><dd>{{ $event->venue ?: 'Venue to be announced' }}<span>{{ $event->city }}</span></dd></div>
                <div><dt>Status</dt><dd>{{ $event->display_status }}</dd></div>
            </dl>
            <a class="events-button events-button--outline" href="{{ route('events.calendar', $event->slug) }}">Add to Calendar <span aria-hidden="true">+</span></a>
            @if($event->safe_ticket_url)<a class="events-button" href="{{ $event->safe_ticket_url }}" target="_blank" rel="noopener noreferrer">Official event / tickets <span aria-hidden="true">↗</span></a>@endif
        </aside>
    </div>
    @if($related->isNotEmpty())<section class="event-detail__related"><div class="events-section-heading" data-event-reveal><div><p class="events-kicker">KEEP EXPLORING</p><h2>More in your <em>universe.</em></h2></div></div><div class="events-grid" data-event-grid>@foreach($related as $relatedEvent)@include('events.partials.event-card', ['event' => $relatedEvent])@endforeach</div></section>@endif
</article>
@endsection
