@props(['content', 'rank'])
<article class="trending-card trending-card--{{ $rank }} home-tone--{{ $content->category?->slug ?? 'fandom' }}" data-stagger-item>
    <a href="{{ route('public.content', $content->slug) }}" class="trending-card-link" aria-labelledby="trending-title-{{ $content->id }}">
        <img class="trending-card-image" src="{{ asset(config('homepage.images.trending')) }}" width="970" height="545" alt="" loading="{{ $rank === 1 ? 'eager' : 'lazy' }}" @if($rank === 1) fetchpriority="high" @endif decoding="async">
        <span class="trending-rank" aria-label="Rank {{ $rank }}">{{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}</span>
        <x-fandom-badge :slug="$content->category?->slug" :label="$content->category?->name ?? 'Fandom'" />
        <div class="trending-card-copy">
            <h3 id="trending-title-{{ $content->id }}">{{ $content->title }}</h3>
            @if($content->excerpt)<p>{{ $content->excerpt }}</p>@endif
            <span class="trending-card-cta"><span class="home-round-arrow"><x-site-icon name="next" /></span>@if($rank === 1)<span>Explore Fandom</span><x-site-icon name="arrow" />@endif</span>
        </div>
    </a>
</article>
