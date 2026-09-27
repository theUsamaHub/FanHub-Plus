<article class="event-card" data-event-card>
    <a class="event-card__link" href="{{ $event->slug ? route('events.show', $event->slug) : route('events.index') }}">
        <div class="event-card__art"><img src="{{ $event->artwork_url }}" data-event-image data-fallback="{{ $event->fallback_artwork }}" alt="{{ $event->coverMedia?->alt_text ?: $event->title }}" width="720" height="480" loading="lazy"><span class="event-status">{{ $event->display_status }}</span><span class="event-date"><b>{{ $event->start_at->format('d') }}</b>{{ strtoupper($event->start_at->format('M')) }}</span></div>
        <div class="event-card__body">
            <p class="event-card__category">{{ $event->category?->name ?? 'All fandoms' }}@if($event->type_label)<span> / {{ $event->type_label }}</span>@endif</p>
            <h3>{{ $event->title }}</h3>
            @if($event->distance_km !== null)<p class="event-card__distance">{{ number_format($event->distance_km, 1) }} km away</p>@endif
            <p class="event-card__location">{{ $event->city }}<span aria-hidden="true"> · </span><time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('M j, Y') }}</time></p>
            <span class="event-card__view">View Event <x-site-icon name="arrow" /></span>
        </div>
    </a>
    @if($event->map_url)<a class="event-card__map" href="{{ $event->map_url }}" target="_blank" rel="noopener noreferrer">View on Map <span class="visually-hidden">for {{ $event->title }}</span></a>@endif
</article>
