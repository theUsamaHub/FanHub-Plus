<div class="events-filter-toolbar"><span>YOUR PLANS, YOUR WAY</span><button class="events-filter-toggle" type="button" data-open-event-filters hidden>Filters <x-site-icon name="settings" /></button></div>
<div class="events-filter-panel" id="events-filters" data-event-filter-panel>
    <div class="events-filter-panel__heading"><h3 id="event-filter-title">Find your event</h3><button type="button" aria-label="Close filters" data-close-event-filters><x-site-icon name="close" /></button></div>
    <form class="events-filter-form" method="GET" action="{{ route('events.index') }}#explore-events">
        <label class="events-search-label">Search<input type="search" name="q" maxlength="120" placeholder="Event, artist, venue…" value="{{ $filters['q'] ?? '' }}"></label>
        <label>Fandom<select name="category"><option value="">All fandoms</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>
        <label>City<select name="city"><option value="">Every city</option>@foreach($cities as $city)<option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>@endforeach</select></label>
        <label>Event type<select name="type"><option value="">Every type</option>@foreach(config('events.types') as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>On this date<input type="date" name="date" value="{{ $filters['date'] ?? '' }}"></label>
        <label>When<select name="when"><option value="">Any time</option><option value="upcoming" @selected(($filters['when'] ?? '') === 'upcoming')>Upcoming</option><option value="past" @selected(($filters['when'] ?? '') === 'past')>Past events</option></select></label>
        <label>Sort by<select name="sort"><option value="">Soonest first</option><option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>Most popular</option><option value="latest" @selected(($filters['sort'] ?? '') === 'latest')>Recently added</option></select></label>
        <div class="events-filter-actions"><button class="events-button" type="submit">Find events <x-site-icon name="arrow" /></button><a href="{{ route('events.index') }}#explore-events">Reset</a></div>
        @if($errors->any())<p class="events-filter-error" role="alert">{{ $errors->first() }}</p>@endif
    </form>
</div>
