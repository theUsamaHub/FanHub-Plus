@props(['card', 'compact' => false])
@php
    $type = $card['type'] ?? 'article';
    $typeLabels = [
        'article' => ['verb' => 'Read', 'label' => 'Article'],
        'video' => ['verb' => 'Watch', 'label' => 'Video'],
        'audio' => ['verb' => 'Listen', 'label' => 'Audio'],
        'image' => ['verb' => 'View', 'label' => 'Image'],
    ];
    $t = $typeLabels[$type] ?? $typeLabels['article'];
@endphp
<a class="member-art-card {{ $compact ? 'member-art-card--compact' : '' }}" href="{{ $card['url'] }}">
    <img src="{{ $card['image'] }}" alt="" loading="lazy" data-image-fallback="{{ asset('images/fandoms/anime.png') }}">
    <div><span class="member-tag">{{ $card['category'] }}</span><h3>{{ $card['title'] }}</h3>@unless($compact)<span class="member-art-card__hint">{{ $t['verb'] }} {{ Str::lower($t['label'] ) }} <span aria-hidden="true">↗</span></span>@endunless</div>
</a>
