@props(['story'])
<article class="fandom-story" data-card-reveal>
    <a href="{{ route('public.content', $story->slug) }}" class="fandom-story__link">
        <div class="fandom-story__art">
            <img src="{{ $story->artwork_url }}" alt="{{ $story->cover?->alt_text ?? '' }}" width="720" height="480" loading="lazy" decoding="async" data-image-fallback="{{ asset(config('homepage.artwork.'.($story->category?->slug ?? 'anime'), config('homepage.images.trending'))) }}">
            <span class="fandom-story__badge">{{ $story->category?->name ?? 'Stories' }}</span>
            @if($story->is_featured)<span class="fandom-story__featured">EDITOR'S PICK</span>@endif
            <span class="fandom-story__arrow" aria-hidden="true"><x-site-icon name="arrow" /></span>
        </div>
        <div class="fandom-story__body">
            <p class="fandom-story__meta"><span>{{ ucfirst($story->type ?? 'article') }}</span><span>{{ $story->reading_minutes }} min read</span></p>
            <h3>{{ $story->title }}</h3>
            @if($story->excerpt)<p class="fandom-story__excerpt">{{ $story->excerpt }}</p>@endif
            <div class="fandom-story__foot"><span>{{ $story->published_at?->format('M d, Y') ?? 'Discover the story' }}</span><span>Read story ↗</span></div>
        </div>
    </a>
</article>
