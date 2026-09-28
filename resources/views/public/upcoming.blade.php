@extends('layouts.public')
@section('title', 'Upcoming Releases | Fan Hub Plus')

@push('styles')
    <style>
        .up-page { --up-card: #161126; --up-card-line: #2c2447; --up-poster-fade: #161126; --up-chip: #ffd84d; --up-chip-ink: #241703; --up-orange: #ff7a2f; --up-label-bg: #ffc94d14; --up-label-line: #ffc94d66; --up-label-ink: #ffc94d; position: relative; }
        :root[data-theme='light'] .up-page { --up-card: #fff; --up-card-line: #ece5f5; --up-poster-fade: #fff; --up-label-bg: #fff3d0; --up-label-line: #f0db99; --up-label-ink: #7c5210; }

        .up-crumb { display: flex; align-items: center; gap: 9px; padding: 4px 0 20px; font-size: 12.5px; color: var(--fh-muted); }
        .up-crumb a { display: inline-flex; align-items: center; gap: 7px; color: var(--fh-muted); transition: color .2s; }
        .up-crumb a:hover { color: var(--up-orange); }
        .up-crumb a svg { width: 15px; height: 15px; }
        .up-crumb__sep { opacity: .55; }

        .up-hero { position: relative; margin-top: 4px; padding: 28px 30px 26px; border: 1px solid var(--fh-line); border-radius: 18px; background: var(--fh-surface); }
        .up-eyebrow { margin: 0 0 10px; color: var(--up-orange); font-size: 11px; font-weight: 700; letter-spacing: 4.5px; text-transform: uppercase; }
        .up-hero h1 { margin: 0; font-family: 'Rajdhani', sans-serif; font-size: clamp(34px, 4.8vw, 58px); font-weight: 700; line-height: 1.06; letter-spacing: -.5px; color: var(--fh-text); }
        .up-hero h1 .fan-gradient-text { background: linear-gradient(105deg, #ff7a3c 2%, #f0416b 55%, #e14f9a 100%); background-clip: text; -webkit-text-fill-color: transparent; }
        :root[data-theme='light'] .up-hero h1 .fan-gradient-text { background-image: linear-gradient(105deg, #e8600f, #dc2f5c 55%, #c93b86); }
        .up-hero .up-sub { max-width: 640px; margin: 16px 0 0; color: var(--fh-muted); font-size: 14.5px; line-height: 1.75; }

        .up-stats { display: flex; flex-wrap: wrap; margin-top: 30px; }
        .up-stats__item { display: flex; align-items: baseline; gap: 9px; padding: 0 24px; border-right: 1px solid var(--fh-line); font-size: 13px; color: var(--fh-muted); }
        .up-stats__item:first-child { padding-left: 0; }
        .up-stats__item:last-child { border-right: 0; }
        .up-stats__item strong { color: var(--up-orange); font: 700 23px/1 'Rajdhani', sans-serif; letter-spacing: .5px; }

        .up-toolbar { position: relative; margin-top: 34px; }
        .up-filters { display: flex; gap: 10px; overflow-x: auto; padding: 4px 6px 4px 0; scrollbar-width: none; scroll-behavior: smooth; }
        .up-filters::-webkit-scrollbar { display: none; }
        .up-filters a { flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 45px; padding: 10px 24px; border: 1px solid var(--fh-line); border-radius: 999px; background: var(--fh-surface); color: var(--fh-text); font-size: 13.5px; font-weight: 500; white-space: nowrap; transition: border-color .25s, background .25s, color .25s, transform .2s, box-shadow .25s; }
        .up-filters a:hover { border-color: #c57565; transform: translateY(-1px); }
        .up-filters a[aria-current='true'] { color: #fff; border-color: transparent; background: linear-gradient(100deg, #ff8b3d, #ef4b6e); box-shadow: 0 8px 22px #ef4b6e44; }
        .up-filters .fh-icon { width: 18px; height: 18px; }
        .up-filters__more { position: absolute; z-index: 2; top: 50%; right: 0; display: grid; place-items: center; width: 44px; height: 44px; padding: 0; border: 1px solid var(--fh-line); border-radius: 50%; background: var(--fh-surface); color: var(--fh-text); cursor: pointer; transform: translateY(-50%); transition: border-color .25s, color .25s; }
        .up-filters__more:hover { border-color: var(--up-orange); color: var(--up-orange); }
        .up-filters__more[hidden] { display: none; }
        .up-filters__more .fh-icon { width: 18px; height: 18px; }

        .up-controls { display: flex; align-items: center; gap: 16px; margin-top: 22px; }
        .up-search { position: relative; flex: 1; min-width: 0; }
        .up-search .fh-icon { position: absolute; top: 50%; left: 17px; width: 18px; height: 18px; color: var(--fh-muted); pointer-events: none; transform: translateY(-50%); }
        .up-search input { width: 100%; min-height: 50px; padding: 12px 18px 12px 47px; border: 1px solid var(--fh-line); border-radius: 12px; outline: none; background: var(--fh-surface); color: var(--fh-text); font-size: 14px; transition: border-color .25s, box-shadow .25s; }
        .up-search input::placeholder { color: color-mix(in srgb, var(--fh-muted) 78%, transparent); }
        .up-search input:focus { border-color: var(--up-orange); box-shadow: 0 0 0 3px #ff7a2f29; }
        .up-sort { display: flex; align-items: center; gap: 11px; font-size: 13.5px; color: var(--fh-muted); white-space: nowrap; }
        .up-sort__box { position: relative; display: inline-flex; }
        .up-sort select { min-height: 50px; padding: 11px 42px 11px 16px; border: 1px solid var(--fh-line); border-radius: 12px; outline: none; appearance: none; background: var(--fh-surface); color: var(--fh-text); font-size: 13.5px; cursor: pointer; transition: border-color .25s; }
        .up-sort select:hover, .up-sort select:focus { border-color: var(--up-orange); }
        .up-sort__box .fh-icon { position: absolute; top: 50%; right: 13px; width: 16px; height: 16px; color: var(--fh-muted); pointer-events: none; transform: translateY(-50%); }

        .up-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; margin-top: 32px; }
        .up-card { position: relative; display: flex; flex-direction: column; border: 1px solid var(--up-card-line); border-radius: 16px; background: var(--up-card); overflow: hidden; transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease; }
        .up-card:hover { transform: translateY(-5px); border-color: #c57565; box-shadow: 0 18px 40px #00000044, 0 0 0 1px #c5756533; }
        .up-card__poster { position: relative; display: block; aspect-ratio: 3 / 2; overflow: hidden; background: #0c0a18; }
        .up-card__poster img { width: 100%; height: 100%; object-fit: cover; object-position: center 32%; transition: transform .5s ease; }
        .up-card:hover .up-card__poster img { transform: scale(1.06); }
        .up-card__poster::after { content: ''; position: absolute; inset: 0; background: linear-gradient(transparent 45%, var(--up-poster-fade) 96%); pointer-events: none; }
        .up-card__badge { position: absolute; top: 12px; left: 12px; z-index: 2; max-width: calc(100% - 108px); }
        .up-card__badge .home-badge { display: inline-flex; align-items: center; gap: 7px; max-width: 100%; padding: 6px 13px; border: 1px solid var(--up-chip); border-radius: 999px; background: var(--up-chip); color: var(--up-chip-ink); box-shadow: 0 4px 14px #00000038; font-size: 11px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; }
        .up-card__badge .home-badge .fh-icon { width: 16px; height: 16px; color: var(--up-chip-ink); }
        .up-card__date-chip { position: absolute; top: 12px; right: 12px; z-index: 2; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; min-width: 62px; padding: 8px 10px; border: 1px solid #ffffff1f; border-radius: 12px; background: #0d1230; box-shadow: 0 6px 16px #00000044; color: #fff; line-height: 1.15; text-align: center; }
        .up-card__date-chip strong { font-size: 19px; font-weight: 700; letter-spacing: .5px; }
        .up-card__date-chip span { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.3px; opacity: .9; }
        .up-card__date-chip.is-tba { min-width: 0; padding: 9px 12px; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .up-card__body { display: flex; flex-direction: column; flex: 1; padding: 14px 18px 18px; }
        .up-card__meta { display: flex; align-items: center; gap: 8px; margin: 0 0 9px; color: var(--fh-muted); font-size: 11.5px; font-weight: 600; letter-spacing: .6px; text-transform: uppercase; }
        .up-card__meta .dot { width: 5px; height: 5px; border-radius: 50%; background: var(--up-orange); }
        .up-card h2 { margin: 0 0 12px; font-size: 18px; font-weight: 600; line-height: 1.35; }
        .up-card h2 a { color: var(--fh-text); }
        .up-card h2 a:hover { color: var(--up-orange); }
        .up-card__label { display: inline-flex; align-self: flex-start; margin-top: auto; padding: 5px 14px; border: 1px solid var(--up-label-line); border-radius: 999px; background: var(--up-label-bg); color: var(--up-label-ink); font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
        .up-card__cta { display: inline-flex; align-items: center; gap: 7px; margin-top: 14px; color: var(--up-orange); font-size: 13.5px; font-weight: 600; }
        .up-card__cta .fh-icon { width: 16px; height: 16px; transition: transform .25s; }
        .up-card:hover .up-card__cta .fh-icon { transform: translateX(3px); }
        .up-card:focus-within { outline: 2px solid var(--fh-accent); outline-offset: 4px; }

        .up-empty { grid-column: 1 / -1; padding: 70px 24px; text-align: center; border: 1px dashed var(--fh-line); border-radius: 18px; background: color-mix(in srgb, var(--fh-surface) 70%, transparent); }
        .up-empty h2 { margin: 0 0 10px; font-size: 22px; }
        .up-empty p { margin: 0; color: var(--fh-muted); }

        .up-pagination { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-top: 36px; padding: 14px 18px; border: 1px solid var(--fh-line); border-radius: 14px; background: var(--fh-surface); }
        .up-pagination a, .up-pagination span { display: inline-flex; align-items: center; min-height: 40px; padding: 8px 18px; border-radius: 10px; font-size: 13px; }
        .up-pagination a { border: 1px solid var(--fh-line); color: var(--fh-text); }
        .up-pagination a:hover { border-color: var(--up-orange); color: var(--up-orange); }
        .up-pagination > span { color: var(--fh-muted); }

        @media (hover: hover) and (pointer: fine) {
            .up-card__poster::before { content: ''; position: absolute; z-index: 1; inset: 0; background: linear-gradient(110deg, transparent 25%, #ffffff1f 48%, transparent 70%); transform: translateX(-110%); transition: transform .75s ease; pointer-events: none; }
            .up-card:hover .up-card__poster::before { transform: translateX(110%); }
            .up-card__date-chip { transition: transform .35s ease; }
            .up-card:hover .up-card__date-chip { transform: translateY(3px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .up-card, .up-card__poster img, .up-card__poster::before, .up-card__date-chip, .up-card__cta .fh-icon, .up-filters a { transition: none; }
            .up-card:hover, .up-card:hover .up-card__poster img, .up-card:hover .up-card__date-chip, .up-card:hover .up-card__cta .fh-icon, .up-filters a:hover { transform: none; }
            .up-card__poster::before { display: none; }
            .up-filters { scroll-behavior: auto; }
        }

        @media (max-width: 1000px) { .up-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 700px) {
            .up-crumb { padding-bottom: 14px; }
            .up-hero { padding: 20px 18px; }
            .up-hero h1 { font-size: clamp(30px, 9vw, 40px); }
            .up-stats { gap: 14px 0; }
            .up-stats__item { padding: 0 16px; }
            .up-controls { flex-direction: column; align-items: stretch; gap: 12px; }
            .up-sort { justify-content: space-between; }
            .up-sort__box { flex: 1; }
            .up-sort select { width: 100%; }
            .up-grid { grid-template-columns: 1fr; gap: 16px; }
            .up-pagination { flex-direction: column; text-align: center; }
        }
    </style>
@endpush

@section('content')
<section class="fh-content-page up-page">
    <nav class="up-crumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 15 11-9 11 9"/><path d="M8 13v13h6v-8h4v8h6V13"/></svg>
            Home
        </a>
        <span class="up-crumb__sep" aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">Upcoming Releases</span>
    </nav>

    <header class="up-hero">
        <p class="up-eyebrow">WHAT&rsquo;S NEXT</p>
        <h1>Upcoming <span class="fan-gradient-text">Releases</span></h1>
        <p class="up-sub">Premieres, seasons, drops, and events &mdash; track everything landing across your favorite universes.</p>
        <div class="up-stats" aria-live="polite">
            <span class="up-stats__item"><strong>{{ $stats['total'] }}</strong> Total</span>
            <span class="up-stats__item"><strong>{{ $stats['thisMonth'] }}</strong> This Month</span>
            <span class="up-stats__item"><strong>{{ $stats['fandoms'] }}</strong> All Fandoms</span>
        </div>
    </header>

    @php($keep = http_build_query(array_filter(['q' => $search !== '' ? $search : null, 'sort' => $sort !== 'nearest' ? $sort : null])))
    <div class="up-toolbar">
        <nav class="up-filters" aria-label="Filter upcoming releases" data-up-filters>
            <a href="{{ route('public.section', ['section' => 'upcoming', 'category' => 'all']) }}{{ $keep ? '?'.$keep : '' }}" @if($activeFilter === 'all') aria-current="true" @endif>All</a>
            @foreach($filters as $filter)
                <a href="{{ route('public.section', ['section' => 'upcoming', 'category' => $filter->slug]) }}{{ $keep ? '?'.$keep : '' }}" @if($activeFilter === $filter->slug) aria-current="true" @endif>
                    <x-site-icon :name="config('fandoms.'.$filter->slug.'.icon', 'compass')" />{{ $filter->name }}
                </a>
            @endforeach
            <a href="{{ route('public.section', ['section' => 'upcoming', 'category' => 'merchandise']) }}{{ $keep ? '?'.$keep : '' }}" @if($activeFilter === 'merchandise') aria-current="true" @endif>
                <x-site-icon name="bag" />Merchandise
            </a>
        </nav>
        <button type="button" class="up-filters__more" data-up-scroll hidden aria-label="Show more filters">
            <x-site-icon name="next" />
        </button>
    </div>

    <form class="up-controls" method="GET" action="{{ route('public.section', ['section' => 'upcoming']) }}" role="search" data-up-form>
        <input type="hidden" name="category" value="{{ $activeFilter }}">
        <div class="up-search">
            <x-site-icon name="search" />
            <input type="search" name="q" value="{{ $search }}" placeholder="Search upcoming releases&hellip;" aria-label="Search upcoming releases" autocomplete="off">
        </div>
        <label class="up-sort">
            <span>Sort by:</span>
            <span class="up-sort__box">
                <select name="sort" aria-label="Sort upcoming releases">
                    <option value="nearest" @selected($sort === 'nearest')>Release Date (Nearest)</option>
                    <option value="farthest" @selected($sort === 'farthest')>Release Date (Farthest)</option>
                    <option value="name" @selected($sort === 'name')>Title (A&ndash;Z)</option>
                    <option value="newest" @selected($sort === 'newest')>Newest Added</option>
                </select>
                <x-site-icon name="chevron" />
            </span>
        </label>
    </form>

    <div class="up-grid">
        @forelse($releases as $release)
            <article class="up-card" data-card-reveal>
                <a href="{{ $release['url'] }}" class="up-card__poster" tabindex="-1" aria-hidden="true">
                    <img src="{{ $release['image'] }}" alt="" loading="lazy" decoding="async" width="640" height="426">
                </a>
                <div class="up-card__badge">
                    <x-fandom-badge :slug="$release['slug']" :label="$release['category']" />
                </div>
                @if($release['date'])
                    <div class="up-card__date-chip" title="{{ $release['date']->format('F j, Y') }}">
                        <strong>{{ $release['date']->format('d') }}</strong>
                        <span>{{ $release['date']->format('M Y') }}</span>
                    </div>
                @else
                    <div class="up-card__date-chip is-tba">Date TBA</div>
                @endif
                <div class="up-card__body">
                    <p class="up-card__meta">
                        <span>{{ $release['category'] }}</span>
                        <span class="dot" aria-hidden="true"></span>
                        <span>{{ $release['date']?->diffForHumans(null, true) ?? 'TBA' }}{{ $release['date'] ? ' away' : '' }}</span>
                    </p>
                    <h2><a href="{{ $release['url'] }}">{{ $release['title'] }}</a></h2>
                    <span class="up-card__label">{{ ucwords($release['label']) }}</span>
                    <span class="up-card__cta">Explore release <x-site-icon name="arrow" /></span>
                </div>
            </article>
        @empty
            <div class="up-empty">
                <h2>No upcoming releases yet</h2>
                <p>Check back soon &mdash; new premieres and drops are added regularly.</p>
            </div>
        @endforelse
    </div>

    @if($releases->hasPages())
        <nav class="up-pagination" aria-label="Upcoming release pages">
            @if($releases->previousPageUrl())
                <a href="{{ $releases->previousPageUrl() }}">&larr; Previous</a>
            @else
                <span></span>
            @endif
            <span>Page {{ $releases->currentPage() }} of {{ $releases->lastPage() }}</span>
            @if($releases->nextPageUrl())
                <a href="{{ $releases->nextPageUrl() }}">Next &rarr;</a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
</section>
@endsection

@push('scripts')
<script>
(() => {
    const row = document.querySelector('[data-up-filters]');
    const more = document.querySelector('[data-up-scroll]');
    if (row && more) {
        const sync = () => {
            more.hidden = row.scrollWidth <= row.clientWidth + 8;
            row.style.paddingRight = more.hidden ? '6px' : '60px';
        };
        more.addEventListener('click', () => row.scrollBy({ left: Math.max(220, row.clientWidth * .7), behavior: 'smooth' }));
        row.addEventListener('scroll', sync, { passive: true });
        addEventListener('resize', sync);
        sync();
    }
    const form = document.querySelector('[data-up-form]');
    if (form) {
        let timer;
        const search = form.querySelector('input[type="search"]');
        search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => form.requestSubmit(), 450); });
        form.querySelector('select').addEventListener('change', () => form.requestSubmit());
    }
})();
</script>
@endpush
