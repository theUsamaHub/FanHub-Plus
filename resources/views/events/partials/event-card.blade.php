<article class="event-card" data-event-card>
    <a class="event-card__link" href="{{ route('events.show', $event->slug) }}">
        <div class="event-card__art"><img src="{{ $event->artwork_url }}" data-event-image data-fallback="{{ $event->fallback_artwork }}" alt="{{ $event->coverMedia?->alt_text ?: $event->title }}" width="720" height="480" loading="lazy"><span class="event-status">{{ $event->display_status }}</span><span class="event-date"><b>{{ $event->start_at->format('d') }}</b>{{ strtoupper($event->start_at->format('M')) }}</span></div>
        <div class="event-card__body">
            <p class="event-card__category">{{ $event->category?->name ?? 'All fandoms' }}@if($event->type_label)<span> / {{ $event->type_label }}</span>@endif</p>
            <h3>{{ $event->title }}</h3>
            <p class="event-card__location">{{ $event->city }}<span aria-hidden="true"> · </span><time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('M j, Y') }}</time></p>
            <span class="event-card__view">View Event <x-site-icon name="arrow" /></span>
        </div>
    </a>
</article>
