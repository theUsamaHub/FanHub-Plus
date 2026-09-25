<article class="event-feature" data-featured-event data-position="{{ $position }}">
    <a class="event-feature__link" href="{{ route('events.show', $event->slug) }}">
        <div class="event-feature__art">
            <img src="{{ $event->artwork_url }}" data-event-image data-fallback="{{ $event->fallback_artwork }}" alt="{{ $event->coverMedia?->alt_text ?: $event->title }}" width="1000" height="900" loading="lazy">
            <span class="event-feature__number" aria-hidden="true">{{ str_pad($position, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="event-feature__image-label">THE FANHUB EDIT</span>
        </div>
        <div class="event-feature__body">
            <div class="event-tags"><span>{{ $event->category?->name ?? 'All fandoms' }}</span>@if($event->type_label)<span>{{ $event->type_label }}</span>@endif</div>
            <h3>{{ $event->title }}</h3>
            <p class="event-feature__description">{{ $event->summary }}</p>
            <dl class="event-feature__facts">
                <div><dt>WHEN</dt><dd><time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('d M Y') }}</time><span>{{ $event->start_at->format('g:i A') }} · {{ config('app.timezone') }}</span></dd></div>
                <div><dt>WHERE</dt><dd>{{ $event->city }}<span>{{ $event->venue ?: 'Venue to be announced' }}</span></dd></div>
            </dl>
            <span class="events-button">View Event <x-site-icon name="arrow" /></span>
        </div>
    </a>
</article>
