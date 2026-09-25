@extends('layouts.public')
@section('title', 'Upcoming Releases | Fan Hub Plus')

@push('styles')
    <style>
        .up-hero { position: relative; overflow: hidden; padding: 48px 0 28px; text-align: center; }
        .up-hero::before { content: ''; position: absolute; inset: -40% -10% auto; height: 220%; background: radial-gradient(ellipse at 50% 0%, #7b2fff33, transparent 62%); pointer-events: none; }
        .up-hero::after { content: ''; position: absolute; left: 50%; top: 0; width: min(720px, 90vw); height: 1px; background: linear-gradient(90deg, transparent, #b64ff288, transparent); transform: translateX(-50%); }
        .up-eyebrow { position: relative; margin: 0 0 8px; color: var(--fh-accent); font-size: 11px; font-weight: 600; letter-spacing: 5px; }
        .up-hero h1 { position: relative; margin: 0; font-family: 'Rajdhani', sans-serif; font-size: clamp(38px, 6vw, 72px); font-weight: 700; line-height: 1.05; letter-spacing: -1px; }
        .up-hero h1 .fan-gradient-text { background: linear-gradient(110deg, #ff7a3c 5%, #ed476b 45%, #b64ff2 90%); background-clip: text; -webkit-text-fill-color: transparent; }
        .up-hero p { position: relative; max-width: 560px; margin: 14px auto 0; color: var(--fh-muted); font-size: 15px; }
        .up-filters { position: relative; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin: 28px 0 8px; padding: 0 12px; }
        .up-filters a { display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 9px 22px; border: 1px solid var(--fh-line); border-radius: 999px; background: color-mix(in srgb, var(--fh-surface) 80%, transparent); color: var(--fh-text); font-size: 13px; transition: border-color .25s, background .25s, color .25s, transform .2s; }
        .up-filters a:hover { border-color: #c57565; transform: translateY(-1px); }
        .up-filters a[aria-current='true'] { color: #fff !important; border-color: transparent; background: linear-gradient(105deg, #df702f, #ce435f 52%, #8c43c0); box-shadow: 0 8px 22px #ce435f44; }
        .up-filters .fh-icon { width: 18px; height: 18px; }

        .up-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; margin-top: 28px; }
        .up-card { position: relative; display: flex; flex-direction: column; border: 1px solid var(--fh-line); border-radius: 16px; background: var(--fh-surface); overflow: hidden; transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease; }
        .up-card:hover { transform: translateY(-5px); border-color: #c57565; box-shadow: 0 18px 40px #00000044, 0 0 0 1px #c5756533; }
        .up-card__poster { position: relative; aspect-ratio: 16 / 10; overflow: hidden; background: #0c0a18; }
        .up-card__poster img { width: 100%; height: 100%; object-fit: cover; object-position: center 32%; transition: transform .5s ease; }
        .up-card:hover .up-card__poster img { transform: scale(1.06); }
        .up-card__poster::after { content: ''; position: absolute; inset: 0; background: linear-gradient(transparent 45%, var(--fh-surface) 96%); pointer-events: none; }
        .up-card__badge { position: absolute; top: 12px; left: 12px; z-index: 2; max-width: calc(100% - 130px); }
        .up-card__badge .home-badge { max-width: 100%; }
        .up-card__date-chip { position: absolute; top: 12px; right: 12px; z-index: 2; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; min-width: 58px; padding: 7px 10px; border: 1px solid #ffffff22; border-radius: 12px; background: #0a0614cc; backdrop-filter: blur(8px); color: #fff; line-height: 1.15; }
        .up-card__date-chip strong { font-size: 18px; font-weight: 700; letter-spacing: .5px; }
        .up-card__date-chip span { font-size: 10px; text-transform: uppercase; letter-spacing: 1.4px; opacity: .85; }
        .up-card__date-chip.is-tba { min-width: 0; padding: 8px 12px; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .up-card__body { display: flex; flex-direction: column; flex: 1; padding: 6px 18px 20px; }
        .up-card__meta { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: var(--fh-muted); font-size: 12px; letter-spacing: .4px; text-transform: uppercase; }
        .up-card__meta .dot { width: 4px; height: 4px; border-radius: 50%; background: var(--fh-accent); }
        .up-card h2 { margin: 0 0 10px; font-size: 19px; font-weight: 600; line-height: 1.3; }
        .up-card h2 a { color: var(--fh-text); }
        .up-card h2 a:hover { color: var(--fh-accent); }
        .up-card__label { display: inline-flex; align-self: flex-start; margin-top: auto; padding: 5px 13px; border: 1px solid #c5756555; border-radius: 999px; background: #c5756514; color: #f0b9a8; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        :root[data-theme='light'] .up-card__label { color: #a8482e; background: #c5756518; border-color: #c5756544; }
        .up-card__cta { display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; color: var(--fh-accent); font-size: 13px; font-weight: 600; }
        .up-card__cta .fh-icon { width: 16px; height: 16px; transition: transform .25s; }
        .up-card:hover .up-card__cta .fh-icon { transform: translateX(3px); }

        .up-empty { grid-column: 1 / -1; padding: 70px 24px; text-align: center; border: 1px dashed var(--fh-line); border-radius: 18px; background: color-mix(in srgb, var(--fh-surface) 70%, transparent); }
        .up-empty h2 { margin: 0 0 10px; font-size: 22px; }
        .up-empty p { margin: 0; color: var(--fh-muted); }

        .up-pagination { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-top: 36px; padding: 14px 18px; border: 1px solid var(--fh-line); border-radius: 14px; background: var(--fh-surface); }
        .up-pagination a, .up-pagination span { display: inline-flex; align-items: center; min-height: 40px; padding: 8px 18px; border-radius: 10px; font-size: 13px; }
        .up-pagination a { border: 1px solid var(--fh-line); color: var(--fh-text); }
        .up-pagination a:hover { border-color: var(--fh-accent); color: var(--fh-accent); }
        .up-pagination > span { color: var(--fh-muted); }

        .up-stats { display: flex; justify-content: center; gap: 28px; margin: 22px 0 0; color: var(--fh-muted); font-size: 13px; }
        .up-stats strong { color: var(--fh-text); font-size: 18px; font-weight: 700; margin-right: 6px; }

        @media (max-width: 1000px) { .up-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 700px) {
            .up-hero { padding: 30px 0 18px; }
            .up-grid { grid-template-columns: 1fr; gap: 16px; }
            .up-filters { justify-content: flex-start; flex-wrap: nowrap; overflow-x: auto; padding-bottom: 6px; scrollbar-width: thin; }
            .up-filters a { flex-shrink: 0; }
            .up-pagination { flex-direction: column; text-align: center; }
            .up-stats { flex-wrap: wrap; gap: 14px 22px; }
        }
    </style>
@endpush

@section('content')
<section class="fh-content-page">
    <header class="up-hero">
        <p class="up-eyebrow">WHAT’S NEXT</p>
        <h1>Upcoming <span class="fan-gradient-text">Releases</span></h1>
        <p>Premieres, seasons, drops, and events — track everything landing across your favorite universes.</p>
        <div class="up-stats" aria-live="polite">
            <span><strong>{{ $releases->total() }}</strong> total</span>
            <span><strong>{{ $releases->count() }}</strong> on this page</span>
            <span><strong>{{ $activeFilter === 'all' ? 'All' : ($filters->firstWhere('slug', $activeFilter)?->name ?? ucfirst($activeFilter)) }}</strong> fandom</span>
        </div>
    </header>

    <nav class="up-filters" aria-label="Filter upcoming releases">
        <a href="{{ route('public.section', ['section' => 'upcoming', 'category' => 'all']) }}" @if($activeFilter === 'all') aria-current="true" @endif>All</a>
        @foreach($filters as $filter)
            <a href="{{ route('public.section', ['section' => 'upcoming', 'category' => $filter->slug]) }}" @if($activeFilter === $filter->slug) aria-current="true" @endif>
                <x-site-icon :name="config('fandoms.'.$filter->slug.'.icon', 'compass')" />{{ $filter->name }}
            </a>
        @endforeach
        <a href="{{ route('public.section', ['section' => 'upcoming', 'category' => 'merchandise']) }}" @if($activeFilter === 'merchandise') aria-current="true" @endif>
            <x-site-icon name="bag" />Merchandise
        </a>
    </nav>

    <div class="up-grid">
        @forelse($releases as $release)
            <article class="up-card">
                <a href="{{ $release['url'] }}" class="up-card__poster" tabindex="-1" aria-hidden="true">
                    <img src="{{ $release['image'] }}" alt="" loading="lazy" decoding="async" width="640" height="400">
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
                <p>Check back soon — new premieres and drops are added regularly.</p>
            </div>
        @endforelse
    </div>

    @if($releases->hasPages())
        <nav class="up-pagination" aria-label="Upcoming release pages">
            @if($releases->previousPageUrl())
                <a href="{{ $releases->previousPageUrl() }}">← Previous</a>
            @else
                <span></span>
            @endif
            <span>Page {{ $releases->currentPage() }} of {{ $releases->lastPage() }}</span>
            @if($releases->nextPageUrl())
                <a href="{{ $releases->nextPageUrl() }}">Next →</a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
</section>
@endsection
