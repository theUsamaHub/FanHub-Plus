<section class="home-section merch-section" id="merchandise" aria-labelledby="merch-title" data-merch-section>
    <header class="merch-heading">
        <p class="merch-eyebrow">FANHUB PLUS</p>
        <h2 id="merch-title">OFFICIAL <span class="fan-gradient-text">MERCHANDISE</span></h2>
        <p class="merch-subtitle">Show your fandom. Wear the story.</p>
        <a class="merch-view-all" href="{{ route('public.section', 'merchandise') }}">View All <x-site-icon name="arrow" /></a>
    </header>
    <nav class="merch-filters" aria-label="Merchandise categories">
        @foreach(['all' => ['name' => 'All'], ...config('fandoms')] as $slug => $category)
            <a href="{{ route('home', array_merge(request()->only('release_category'), ['merch_category' => $slug])) }}#merchandise" data-merch-filter="{{ $slug }}" @if($activeMerchFilter === $slug) aria-current="true" @endif>{{ $category['name'] }}</a>
        @endforeach
    </nav>
    <div class="merch-carousel">
        <button type="button" class="merch-arrow merch-arrow--prev" aria-label="Previous merchandise" hidden><x-site-icon name="next" /></button>
        @include('home.sections.merchandise-results')
        <button type="button" class="merch-arrow merch-arrow--next" aria-label="Next merchandise" hidden><x-site-icon name="next" /></button>
    </div>
    <p class="merch-feedback" data-merch-feedback role="status" aria-live="polite"></p>
</section>
