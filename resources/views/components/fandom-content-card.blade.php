@props(['content'])
@php
    $type = $content->type ?? 'article';
    $typeLabels = [
        'article' => ['verb' => 'Read', 'icon' => 'book', 'label' => 'Article'],
        'video' => ['verb' => 'Watch', 'icon' => 'play-circle', 'label' => 'Video'],
        'audio' => ['verb' => 'Listen', 'icon' => 'music-note', 'label' => 'Audio'],
        'image' => ['verb' => 'View', 'icon' => 'image', 'label' => 'Image'],
    ];
    $t = $typeLabels[$type] ?? $typeLabels['article'];
@endphp
<article class="fandom-content" data-card-reveal>
    <a href="{{ route('public.content', $content->slug) }}" class="fandom-content__link">
        <div class="fandom-content__art">
            <img src="{{ $content->artwork_url }}" alt="{{ $content->cover?->alt_text ?? '' }}" width="720" height="480" loading="lazy" decoding="async" data-image-fallback="{{ asset(config('homepage.artwork.'.($content->category?->slug ?? 'anime'), config('homepage.images.trending'))) }}">
            <span class="fandom-content__badge">{{ $content->category?->name ?? 'Content' }}</span>
            <span class="fandom-content__type fandom-content__type--{{ $type }}"><x-site-icon name="{{ $t['icon'] }}" /> {{ $t['label'] }}</span>
            @if($content->is_featured)<span class="fandom-content__featured">EDITOR'S PICK</span>@endif
            <span class="fandom-content__arrow" aria-hidden="true"><x-site-icon name="arrow" /></span>
        </div>
        <div class="fandom-content__body">
            <p class="fandom-content__meta"><span>{{ $t['label'] }}</span>@if($type === 'article')<span>{{ $content->reading_minutes }} min read</span>@endif</p>
            <h3>{{ $content->title }}</h3>
            @if($content->excerpt)<p class="fandom-content__excerpt">{{ $content->excerpt }}</p>@endif
            <div class="fandom-content__foot"><span>{{ $content->published_at?->format('M d, Y') ?? 'Discover' }}</span><span>{{ $t['verb'] }} {{ Str::lower($t['label']) }} <x-site-icon name="arrow" /></span></div>
        </div>
    </a>
</article>
