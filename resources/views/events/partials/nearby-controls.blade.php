<div class="events-nearby" data-nearby-controls data-endpoint="{{ route('events.nearby.search') }}" hidden>
    <div class="events-nearby__radar" aria-hidden="true"><i></i><i></i><span><x-site-icon name="compass" /></span><b></b></div>
    <div class="events-nearby__copy"><p class="events-kicker">YOUR WORLD. A LITTLE CLOSER.</p><h3>Great moments.<br><em>Right around you.</em></h3><p>Find your next meetup, screening or fan gathering.<br>Start within 5 km. See where it takes you.</p><span class="events-nearby__privacy"><x-site-icon name="compass" /> Only shared when you choose. Never saved.</span></div>
    <div class="events-nearby__actions">
        <button class="events-button" type="button" data-nearby-start>Show Nearby Events <x-site-icon name="compass" /></button>
        <label hidden data-nearby-radius-label>Search radius<select data-nearby-radius>@foreach(config('events.nearby_radii') as $radius)<option value="{{ $radius }}">{{ $radius }} km</option>@endforeach</select></label>
        <button class="events-button events-button--outline" type="button" data-nearby-reset hidden>Show all events</button>
        <button class="events-button events-button--outline" type="button" data-nearby-clear-filters hidden>Clear filters &amp; search nearby</button>
    </div>
    <div class="events-nearby__location" data-nearby-location hidden>
        <span data-nearby-accuracy></span>
        <a data-nearby-map target="_blank" rel="noopener noreferrer">Check detected location on map ↗</a>
        <p>If this pin is not where you are, turn on device location and try Show Nearby Events again.</p>
    </div>
    <p class="events-nearby__status" role="status" aria-live="polite" data-nearby-status><span>Ready to explore? Start with your location.</span></p>
</div>
