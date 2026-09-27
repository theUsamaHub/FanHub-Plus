@php
    $pageUrl = $landing ? route('public.fandom', $activeSlug) : route('public.explore');
    $resetUrl = $landing ? $pageUrl : route('public.explore', array_filter(['category' => $activeSlug]));
    $hasFilters = collect($filters)->except('category')->contains(fn ($value) => filled($value));
    $headlines = ['anime' => ['Beyond the', 'last episode.'], 'gaming' => ['A world beyond', 'the next level.'], 'k-pop' => ['More than music.', 'Your whole world.']];
    $headline = $headlines[$activeSlug] ?? ($activeSlug ? [$pageTitle.'.', 'Your universe.'] : ['Find your next', 'obsession.']);
    $heroContent = $landing ? ($featured->first() ?? $trending->first()) : null;
    $heroArt = $heroContent?->artwork_url ?? ($activeCategory?->iconMedia?->isImage() ? $activeCategory->iconMedia->url : asset(config('homepage.artwork.'.$activeSlug, 'images/hero/fandom-cards.png')));
    $portalCategories = $categories->sortBy(fn ($item) => array_search($item->slug, ['anime', 'gaming', 'k-pop']) === false ? 9 : array_search($item->slug, ['anime', 'gaming', 'k-pop']))->take(3);
@endphp
<div class="discovery discovery--{{ $activeSlug ?: 'all' }}" data-discovery>
    <div class="discovery-shell">
        <nav class="discovery-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('public.explore') }}">Explore</a>@if($activeSlug)<span>/</span><span aria-current="page">{{ $pageTitle }}</span>@endif<span class="discovery-edition">FAN CULTURE, WITHOUT LIMITS.</span></nav>
        <section class="discovery-hero" aria-labelledby="discovery-title">
            <div class="discovery-hero__copy" data-discovery-reveal>
                <p class="discovery-kicker"><span></span>{{ $activeSlug ? strtoupper($pageTitle).' / YOUR WORLD STARTS HERE' : 'EVERY UNIVERSE. ONE HOME.' }}</p>
                <h1 id="discovery-title">{{ $headline[0] }}<br><em>{{ $headline[1] }}</em></h1>
                <p class="discovery-hero__description">{{ !empty($identity['lines']) ? implode(' ', $identity['lines']) : 'The stories you stay up for. The worlds you get lost in. Find the things that make you, you.' }}</p>
                <div class="discovery-actions"><a class="discovery-button" href="#fandom-content">{{ $activeSlug ? 'Explore '.$pageTitle : 'Start exploring' }} <x-site-icon name="arrow" /></a><a class="discovery-text-link" href="#choose-universe">{{ $activeSlug ? 'Switch your universe' : 'Find your fandom' }} <span aria-hidden="true">↗</span></a></div>
                <div class="discovery-hero__foot"><span><b>{{ number_format($landing ? $stats['total_content'] : $contents->total()) }}</b> stories &amp; discoveries</span><span><b>{{ $categories->count() }}</b> worlds. One community.</span></div>
            </div>
            <div class="discovery-hero__visual" data-discovery-reveal>
                @if($activeSlug)
                    <div class="discovery-cover">
                        <img src="{{ $heroArt }}" alt="" fetchpriority="high" width="1000" height="1100" data-image-fallback="{{ asset(config('homepage.images.trending')) }}">
                        <span class="discovery-cover__label"><x-site-icon name="{{ $identity['icon'] ?? 'compass' }}" /> THE {{ strtoupper($pageTitle) }} EDIT</span>
                        <div class="discovery-cover__caption"><span>{{ $heroContent ? 'IN THE SPOTLIGHT' : 'WELCOME TO YOUR WORLD' }}</span><h2>{{ $heroContent?->title ?? $pageTitle.' lives here.' }}</h2>@if($heroContent)<a href="{{ route('public.content', $heroContent->slug) }}">Open the story <x-site-icon name="arrow" /></a>@else<p>Discover something worth sharing.</p>@endif</div>
                    </div>
                    <span class="discovery-stamp" aria-hidden="true">{{ $activeSlug === 'gaming' ? 'PLAYER ONE' : ($activeSlug === 'k-pop' ? 'ON REPEAT' : 'FOR THE FANS') }} <b>✦</b></span>
                @else
                    <div class="discovery-collage">
                        @forelse($portalCategories as $portal)
                            <a class="discovery-poster discovery-poster--{{ $loop->iteration }}" href="{{ route('public.fandom', $portal->slug) }}">
                                <img src="{{ asset(config('homepage.artwork.'.$portal->slug, 'images/fandoms/anime.png')) }}" alt="" width="420" height="600" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"><span><small>STEP INTO</small><strong>{{ $portal->name }}</strong><x-site-icon name="arrow" /></span>
                            </a>
                        @empty
                            <img class="discovery-collage__fallback" src="{{ asset('images/hero/fandom-cards.png') }}" alt="A collection of fandom worlds" width="900" height="700">
                        @endforelse
                    </div>
                    <span class="discovery-stamp" aria-hidden="true">A LITTLE OBSESSED <b>✦</b></span>
                @endif
            </div>
        </section>
        <nav class="discovery-universes" id="choose-universe" aria-label="Choose a fandom">
            <span>CHOOSE YOUR<br><b>UNIVERSE</b></span><div><a href="{{ route('public.explore') }}" @if(!$activeSlug) aria-current="page" @endif><x-site-icon name="compass" />All worlds</a>@foreach($categories as $world)<a href="{{ route('public.fandom', $world->slug) }}" @if($activeSlug === $world->slug) aria-current="page" @endif><x-site-icon name="{{ config('fandoms.'.$world->slug.'.icon', 'compass') }}" />{{ $world->name }}</a>@endforeach</div>
        </nav>
        @if($landing && $featured->isNotEmpty() && !$hasFilters && $contents->currentPage() === 1)
            <section class="discovery-picks" aria-labelledby="picks-title">
                <header class="discovery-heading" data-discovery-reveal><div><p class="discovery-kicker">WORTH YOUR ATTENTION</p><h2 id="picks-title">The <em>spotlight.</em></h2></div><a class="discovery-text-link" href="#fandom-content">All discoveries ↗</a></header>
                <div class="discovery-picks__grid">@foreach($featured->take(3) as $pick)<x-fandom-content-card :content="$pick" />@endforeach</div>
            </section>
        @endif
        <section class="discovery-library" id="fandom-content" aria-labelledby="library-title">
            <header class="discovery-heading" data-discovery-reveal><div><p class="discovery-kicker">THE GOOD STUFF, ALL IN ONE PLACE</p><h2 id="library-title">{{ $activeSlug ? 'Inside '.$pageTitle.'.' : 'Made for your' }} @if(!$activeSlug)<em>curiosity.</em>@endif</h2></div><p>New perspectives.<br>More reasons to stay curious.</p></header>
            @auth
                @if($activeCategory)
                    @php($following = auth()->user()->favoriteCategories()->where('categories.id', $activeCategory->id)->exists())
                    <form class="discovery-follow" method="post" action="{{ route('user.favorites.store', $activeCategory) }}">@csrf<input type="hidden" name="saved" value="{{ $following ? 0 : 1 }}"><span><x-site-icon name="heart" /> Make this world part of yours.</span><button type="submit" aria-pressed="{{ $following ? 'true' : 'false' }}">{{ $following ? 'Following '.$pageTitle : 'Follow '.$pageTitle }} <span aria-hidden="true">{{ $following ? '✓' : '+' }}</span></button></form>
                    @include('user.partials.messages')
                @endif
            @endauth
            <form class="discovery-search" action="{{ $pageUrl }}#fandom-content" method="get" role="search">
                <label class="discovery-search__query">Find your next favorite<div><x-site-icon name="search" /><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="Search stories, artists, worlds..."></div></label>
                @if(!$landing)<label>Universe<select name="category"><option value="">All fandoms</option>@foreach($categories as $world)<option value="{{ $world->slug }}" @selected($activeSlug === $world->slug)>{{ $world->name }}</option>@endforeach</select></label>@endif
                <label>Sort by<select name="sort">@foreach(['latest' => 'Latest discoveries', 'popular' => 'Most popular', 'alphabetical' => 'A to Z'] + ($landing ? ['trending' => 'Trending now'] : []) as $key => $label)<option value="{{ $key }}" @selected(($filters['sort'] ?? 'latest') === $key)>{{ $label }}</option>@endforeach</select></label>
                <button class="discovery-button" type="submit">Find it <x-site-icon name="arrow" /></button>
                <details class="discovery-refine" @if(!empty($filters['type']) || !empty($filters['tag']) || !empty($filters['year'])) open @endif><summary>Refine your discovery <span>Type, genre &amp; year <b aria-hidden="true">+</b></span></summary><div>
                    <label>Content type<select name="type"><option value="">Everything</option>@foreach(['article','video','audio','image'] as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></label>
                    <label>Genre / tag<select name="tag"><option value="">All tags</option>@foreach($tags as $tag)<option value="{{ $tag->id }}" @selected(($filters['tag'] ?? '') == $tag->id)>{{ $tag->name }}</option>@endforeach</select></label>
                    <label>Release year<input type="number" name="year" min="1900" max="2200" placeholder="Any year" value="{{ $filters['year'] ?? '' }}"></label>
                    <button class="discovery-button discovery-button--outline" type="submit">Apply filters <x-site-icon name="arrow" /></button>
                </div></details>
                @if(!empty($filters['featured']))<input type="hidden" name="featured" value="1">@endif
            </form>
            @if($errors->any())<p class="discovery-error" role="alert">{{ $errors->first() }}</p>@endif
            <div class="discovery-results"><span><b>{{ number_format($contents->total()) }}</b> {{ Str::plural('discovery', $contents->total()) }}{{ !empty($filters['q']) ? ' for “'.$filters['q'].'”' : ' to get lost in' }}</span>@if($hasFilters)<a href="{{ $resetUrl }}#fandom-content">Clear filters ↗</a>@else<span>READ / WATCH / LISTEN / DISCOVER</span>@endif</div>
            <div class="discovery-grid">
                @forelse($contents as $content)<x-fandom-content-card :content="$content" />
                @empty<div class="discovery-empty"><span><x-site-icon name="{{ $identity['icon'] ?? 'compass' }}" /></span><p class="discovery-kicker">KEEP YOUR CURIOSITY</p><h3>A new chapter is on its way.</h3><p>No content found yet. Try another search or fandom.</p><a class="discovery-button" href="{{ $hasFilters ? $resetUrl : route('public.explore') }}">{{ $hasFilters ? 'Clear filters' : 'Explore all content' }} <x-site-icon name="arrow" /></a></div>@endforelse
            </div>
            @if($contents->hasPages())<nav class="discovery-pagination" aria-label="Search results pages">@if($contents->previousPageUrl())<a href="{{ $contents->previousPageUrl() }}#fandom-content" rel="prev">← Previous</a>@else<span aria-disabled="true">← Previous</span>@endif<span>Page <b>{{ $contents->currentPage() }}</b> of {{ $contents->lastPage() }}</span>@if($contents->nextPageUrl())<a href="{{ $contents->nextPageUrl() }}#fandom-content" rel="next">Next →</a>@else<span aria-disabled="true">Next →</span>@endif</nav>@endif
        </section>
        <section class="discovery-invitation" data-discovery-reveal><span class="discovery-invitation__mark" aria-hidden="true">✦</span><div><p class="discovery-kicker">THERE'S ROOM FOR YOUR VOICE</p><h2>Don't just follow the story.<br><em>Be part of it.</em></h2><p>Share a perspective, a creation, a little of your world.</p></div><a class="discovery-button" href="{{ route('user.submissions.create', $activeSlug ? ['category' => $activeSlug] : []) }}">Create something <x-site-icon name="arrow" /></a></section>
    </div>
</div>
