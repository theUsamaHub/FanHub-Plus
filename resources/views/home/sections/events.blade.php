@php($eventFallback = asset(config('homepage.images.events')))
<section id="home-events" class="home-section home-events" data-home-events aria-labelledby="home-events-title">
    <header class="home-section-heading home-events__heading">
        <p class="home-events__eyebrow"><span></span>Special lineup<span></span></p>
        <h2 id="home-events-title">Events</h2>
        <p class="home-section-subtitle">Exclusive events, launches, and activities from your favorite fandoms.</p>
    </header>

    @if($homeEvents->isNotEmpty())
        <div class="home-events__stage">
            <div class="home-events__details">
                @foreach($homeEvents as $event)
                    <article class="home-events__panel {{ $loop->first ? 'is-active' : '' }}" id="home-event-panel-{{ $event->id }}" data-event-panel aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @if(!$loop->first) inert @endif>
                        <span class="home-events__label">Event {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $event->title }}</h3>
                        <dl class="home-events__facts">
                            <div><i class="bi bi-calendar4-week" aria-hidden="true"></i><dt>Date</dt><dd><time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('M d, Y') }}</time>@if($event->end_at && !$event->start_at->isSameDay($event->end_at))<span> – {{ $event->end_at->format('M d, Y') }}</span>@endif</dd></div>
                            <div><i class="bi bi-geo-alt" aria-hidden="true"></i><dt>Location</dt><dd>{{ $event->city ?: ($event->venue ?: 'To be announced') }}</dd></div>
                            <div><i class="bi bi-people" aria-hidden="true"></i><dt>Type</dt><dd>{{ $event->type_label ?: 'Event' }}</dd></div>
                        </dl>
                        <p class="home-events__summary">{{ $event->summary }}</p>
                        <div class="home-events__tags">
                            @if($event->category)<span><i class="bi bi-stars" aria-hidden="true"></i>{{ $event->category->name }}</span>@endif
                            @if($event->venue)<span><i class="bi bi-geo" aria-hidden="true"></i>{{ $event->venue }}</span>@endif
                            <span><i class="bi bi-clock" aria-hidden="true"></i>{{ $event->display_status }}</span>
                        </div>
                        <a class="home-events__cta" href="{{ route('events.show', $event->slug) }}">Learn more <x-site-icon name="arrow" /></a>
                    </article>
                @endforeach
            </div>
            <div class="home-events__deck" role="region" aria-label="Event cards" tabindex="0" data-event-deck>
                <div class="swiper home-events__swiper" data-event-swiper>
                    <div class="swiper-wrapper">
                        @foreach($homeEvents as $event)
                            @php($eventImage = ($event->coverMedia?->isImage() ? $event->coverMedia->url : null) ?: $eventFallback)
                            <div class="swiper-slide home-events__card">
                                <img src="{{ $eventImage }}" data-image-fallback="{{ $eventFallback }}" alt="{{ $event->coverMedia?->alt_text ?: $event->title }}" width="360" height="440" loading="lazy" decoding="async">
                                <span class="home-events__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                @if($event->is_featured)<span class="home-events__featured"><i class="bi bi-star-fill" aria-hidden="true"></i> Featured</span>@endif
                                <div class="home-events__card-copy"><h3>{{ $event->title }}</h3><p>{{ $event->type_label ?: 'Event' }}@if($event->category)<span></span>{{ $event->category->name }}@endif</p></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="home-events__navigation">
                    <button type="button" data-event-prev aria-label="Previous event"><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
                    <span data-event-count aria-live="polite" aria-atomic="true">01 / {{ str_pad($homeEvents->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    <button type="button" data-event-next aria-label="Next event"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
        <div class="home-events__thumbnails" aria-label="Choose an event">
            @foreach($homeEvents as $event)
                @php($eventImage = ($event->coverMedia?->isImage() ? $event->coverMedia->url : null) ?: $eventFallback)
                <button type="button" class="home-events__thumbnail {{ $loop->first ? 'is-active' : '' }}" data-event-select="{{ $loop->index }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="home-event-panel-{{ $event->id }}">
                    <span class="home-events__preview"><img src="{{ $eventImage }}" data-image-fallback="{{ $eventFallback }}" alt="" width="240" height="90" loading="lazy" decoding="async"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></span>
                    <strong>{{ $event->title }}</strong><small>{{ $event->start_at->format('M d') }}@if($event->end_at && !$event->start_at->isSameDay($event->end_at)) – {{ $event->end_at->format('M d') }}@endif, {{ $event->start_at->format('Y') }}</small>
                </button>
            @endforeach
        </div>
        <a class="home-events__all" href="{{ route('events.index') }}">Explore all events <x-site-icon name="arrow" /></a>
    @else
        <p class="home-empty">New events are on their way. Check back for the next fandom gathering.</p>
    @endif
</section>
