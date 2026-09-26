@props(['card', 'compact' => false])
<a class="member-art-card {{ $compact ? 'member-art-card--compact' : '' }}" href="{{ $card['url'] }}">
    <img src="{{ $card['image'] }}" alt="" loading="lazy" data-image-fallback="{{ asset('images/fandoms/anime.png') }}">
    <div><span class="member-tag">{{ $card['category'] }}</span><h3>{{ $card['title'] }}</h3>@unless($compact)<span class="member-art-card__hint">Explore this story <span aria-hidden="true">↗</span></span>@endunless</div>
</a>
