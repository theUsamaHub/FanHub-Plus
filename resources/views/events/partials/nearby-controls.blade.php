<div class="events-nearby" data-nearby-controls data-endpoint="{{ route('events.nearby.search') }}" hidden>
    <div><p class="events-kicker">CLOSER TO YOUR FANDOM</p><p>Discover fan moments around you.</p></div>
    <div class="events-nearby__actions">
        <button class="events-button" type="button" data-nearby-start>Show Nearby Events <x-site-icon name="compass" /></button>
        <label hidden data-nearby-radius-label>Search radius<select data-nearby-radius>@foreach(config('events.nearby_radii') as $radius)<option value="{{ $radius }}">{{ $radius }} km</option>@endforeach</select></label>
        <button class="events-button events-button--outline" type="button" data-nearby-reset hidden>Show all events</button>
    </div>
    <p class="events-nearby__status" role="status" aria-live="polite" data-nearby-status>Your location is used only for this search.</p>
</div>
