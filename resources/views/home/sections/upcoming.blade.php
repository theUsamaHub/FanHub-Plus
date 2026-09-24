<section id="upcoming" class="home-section home-upcoming" aria-labelledby="upcoming-heading" data-upcoming-section>
    <p class="home-side-note home-side-note--left" aria-hidden="true">DIFFERENT<br>WORLDS.<br>BRIGHTER<br>TOMORROW.</p>
    <p class="home-side-note home-side-note--right" aria-hidden="true">FANDOMS<br>TODAY.<br>BRIGHTER<br>TOMORROW.</p>
    <x-home-section-heading id="upcoming-heading" eyebrow="WHAT’S NEXT" title="UPCOMING" accent="RELEASES" subtitle="See what’s coming next across your favorite universes." />
    <div class="release-toolbar">
        <nav class="release-filters" aria-label="Filter upcoming releases" data-release-filters>
            <a href="{{ route('home', ['release_category' => 'all']) }}#upcoming" data-release-filter="all" @if($activeReleaseFilter === 'all') aria-current="true" @endif>All</a>
            @foreach($releaseFilters->filter(fn ($filter) => in_array($filter->slug, ['anime', 'gaming', 'movies', 'tv-shows', $activeReleaseFilter], true)) as $filter)
                <a href="{{ route('home', ['release_category' => $filter->slug]) }}#upcoming" data-release-filter="{{ $filter->slug }}" @if($activeReleaseFilter === $filter->slug) aria-current="true" @endif><x-site-icon :name="config('fandoms.'.$filter->slug.'.icon', 'compass')" />{{ $filter->name }}</a>
            @endforeach
            <a href="{{ route('home', ['release_category' => 'merchandise']) }}#upcoming" data-release-filter="merchandise" @if($activeReleaseFilter === 'merchandise') aria-current="true" @endif><x-site-icon name="bag" />Merchandise</a>
        </nav>
        <a class="home-view-all" href="{{ route('public.section', ['section' => 'upcoming', 'category' => $activeReleaseFilter]) }}" data-releases-all>View All <x-site-icon name="arrow" /></a>
    </div>
    <div class="release-carousel">
        <button type="button" class="release-arrow release-arrow--prev home-round-arrow" aria-label="Previous releases" aria-controls="release-results" data-release-prev hidden><x-site-icon name="next" /></button>
        @include('home.sections.release-results')
        <button type="button" class="release-arrow release-arrow--next home-round-arrow" aria-label="Next releases" aria-controls="release-results" data-release-next hidden><x-site-icon name="next" /></button>
    </div>
    <p class="release-status home-sr-only" role="status" aria-live="polite" data-release-status></p>
</section>
