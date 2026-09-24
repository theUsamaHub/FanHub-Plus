@props(['release'])
<article class="release-card home-tone--{{ $release['slug'] }}" data-release-card data-stagger-item>
    <a href="{{ $release['url'] }}" aria-labelledby="release-title-{{ $release['id'] }}">
        <div class="release-poster"><img src="{{ asset(config('homepage.images.upcoming')) }}" width="1382" height="2048" alt="" loading="lazy" decoding="async"><x-fandom-badge :slug="$release['slug']" :label="$release['category']" /></div>
        <div class="release-date">@if($release['date'])<time datetime="{{ $release['date']->format('Y-m-d') }}" title="{{ $release['date']->format('F j, Y') }}">{{ $release['date']->format('M d') }}@if($release['date']->year !== now()->year)<small>{{ $release['date']->year }}</small>@endif</time>@else<span>Date TBA</span>@endif</div>
        <span class="release-node" aria-hidden="true"></span>
        <div class="release-copy"><h3 id="release-title-{{ $release['id'] }}">{{ $release['title'] }}</h3><span class="release-label">{{ $release['label'] }}</span></div>
    </a>
</article>
