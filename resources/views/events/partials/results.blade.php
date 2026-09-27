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
