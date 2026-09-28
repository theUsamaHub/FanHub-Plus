@extends('layouts.public')
@prepend('scripts')
@endprepend
@section('title', 'Official Merchandise | Fan Hub Plus')
@php $viewMode = request()->cookie('merch_view', 'grid'); @endphp

@push('styles')
    <style>
        .merch-catalog { --up-orange: #ff7a2f; }

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
        .up-hero .up-sub a { color: var(--up-orange); text-decoration: underline; text-underline-offset: 3px; }

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

        @media (prefers-reduced-motion: reduce) {
            .up-filters a { transition: none; }
            .up-filters a:hover { transform: none; }
            .up-filters { scroll-behavior: auto; }
        }

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
        }
    </style>
@endpush

@section('content')
<section class="fh-content-page merch-catalog">
    <nav class="up-crumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 15 11-9 11 9"/><path d="M8 13v13h6v-8h4v8h6V13"/></svg>
            Home
        </a>
        <span class="up-crumb__sep" aria-hidden="true">&rsaquo;</span>
        <span aria-current="page">Merchandise</span>
    </nav>

    <header class="up-hero">
        <p class="up-eyebrow">THE FAN COLLECTION</p>
        <h1>Find your next <span class="fan-gradient-text">favorite.</span></h1>
        @if($linkedContent ?? null)
            <p class="up-sub">Merchandise for <a href="{{ route('public.content', $linkedContent->slug) }}">{{ $linkedContent->title }}</a>.</p>
        @else
            <p class="up-sub">Explore merchandise from the worlds you love. Discover collectibles, limited editions, and upcoming releases.</p>
        @endif
        <div class="up-stats" aria-live="polite">
            <span class="up-stats__item"><strong>{{ number_format($stats['total']) }}</strong> Total</span>
            <span class="up-stats__item"><strong>{{ number_format($stats['upcoming']) }}</strong> Upcoming</span>
            <span class="up-stats__item"><strong>{{ number_format($stats['fandoms']) }}</strong> All Fandoms</span>
        </div>
    </header>

    @if($errors->any())
        <div class="merch-empty" role="alert" style="margin-top: 24px;"><p>{{ $errors->first() }}</p><a href="{{ route('public.section', 'merchandise') }}">Reset filters</a></div>
    @endif

    @php($keep = array_filter([
        'q' => $filters['q'] !== '' ? $filters['q'] : null,
        'sort' => $filters['sort'] !== 'newest' ? $filters['sort'] : null,
        'status' => $filters['status'] !== '' ? $filters['status'] : null,
        'tag' => $filters['tag'] !== '' ? $filters['tag'] : null,
        'content' => ($linkedContent ?? null)?->id ?? null,
    ]))
    <div class="up-toolbar">
        <nav class="up-filters" aria-label="Filter merchandise by category" data-up-filters>
            <a href="{{ route('public.section', array_merge(['section' => 'merchandise', 'category' => 'all'], $keep)) }}" @if($filters['category'] === 'all') aria-current="true" @endif>All</a>
            @foreach($categories as $category)
                <a href="{{ route('public.section', array_merge(['section' => 'merchandise', 'category' => $category->slug], $keep)) }}" @if($filters['category'] === $category->slug) aria-current="true" @endif>
                    <x-site-icon :name="config('fandoms.'.$category->slug.'.icon', 'bag')" />{{ $category->name }}
                </a>
            @endforeach
        </nav>
        <button type="button" class="up-filters__more" data-up-scroll hidden aria-label="Show more filters">
            <x-site-icon name="next" />
        </button>
    </div>

    <form method="get" action="{{ route('public.section', 'merchandise') }}" class="merch-catalog__form" role="search" aria-label="Search merchandise" data-up-form>
        <input type="hidden" name="category" value="{{ $filters['category'] }}">
        @if($linkedContent ?? null)<input type="hidden" name="content" value="{{ $linkedContent->id }}">@endif
        <div class="up-controls">
            <div class="up-search">
                <x-site-icon name="search" />
                <input id="merch-search" type="search" name="q" value="{{ $filters['q'] }}" maxlength="120" placeholder="Search merchandise&hellip;" aria-label="Search merchandise" autocomplete="off">
            </div>
            <label class="up-sort">
                <span>Sort by:</span>
                <span class="up-sort__box">
                    <select id="merch-sort" name="sort" aria-label="Sort merchandise">
                        @foreach(['newest' => 'Newest arrivals', 'popular' => 'Most viewed', 'name' => 'Name: A–Z'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-site-icon name="chevron" />
                </span>
            </label>
        </div>
        <details class="merch-catalog__filters" @if($hasFilters) open @endif>
            <summary>Refine your collection <span>{{ $hasFilters ? 'Filters active' : 'Release status &amp; edition' }}</span></summary>
            <div class="merch-catalog__filter-fields">
                <label for="merch-status">Release status
                    <select id="merch-status" name="status">
                        <option value="">All releases</option>
                        <option value="released" @selected($filters['status'] === 'released')>Released</option>
                        <option value="upcoming" @selected($filters['status'] === 'upcoming')>Upcoming</option>
                    </select>
                </label>
                <label for="merch-tag">Edition
                    <select id="merch-tag" name="tag">
                        <option value="">All editions</option>
                        @foreach(['limited_edition' => 'Limited Edition', 'pre_order' => 'Pre-Order', 'collectible' => 'Collectible', 'standard' => 'Standard'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['tag'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="fh-button" type="submit">Apply filters</button>
            </div>
        </details>
    </form>
    <div class="merch-catalog__results-bar">
        <div class="merch-catalog__results-heading">
            <h2>Merchandise <span>{{ number_format($items->total()) }} {{ Str::plural('item', $items->total()) }}</span></h2>
            @if($hasFilters)<a href="{{ route('public.section', 'merchandise') }}">Clear filters ×</a>@endif
        </div>
        <div class="merch-catalog__view-toggle" role="group" aria-label="View mode">
            <button class="merch-view-btn" data-view="grid" aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}" title="Grid view">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="11" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="1" y="11" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="11" y="11" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </button>
            <button class="merch-view-btn" data-view="list" aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}" title="List view">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="2" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="1" y="7" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="1" y="12" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <line x1="8" y1="4.5" x2="17" y2="4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="8" y1="9.5" x2="17" y2="9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="8" y1="14.5" x2="17" y2="14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
    <div class="merch-catalog__products" id="merch-products" data-view="{{ $viewMode }}">
        @forelse($items as $item)
            <x-merchandise-card :item="$item" :saved="in_array($item->id, $savedMerchandise)" :catalog="true" :list-view="($viewMode === 'list')" />
        @empty
            <div class="merch-empty merch-empty--full">
                <h2>{{ $hasFilters ? 'No merchandise matches your selected filters.' : 'No merchandise is currently available.' }}</h2>
                <p>{{ $hasFilters ? 'Try a different name or broaden your filters to discover more.' : 'Check back soon for new additions to the collection.' }}</p>
                @if($hasFilters)<a class="fh-button" href="{{ route('public.section', 'merchandise') }}">Browse all merchandise</a>@endif
            </div>
        @endforelse
    </div>
    @if($items->count())
        <p class="merch-pagination-summary">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ number_format($items->total()) }} items</p>
    @endif
    {{ $items->onEachSide(1)->links('public.partials.merchandise-pagination') }}
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
