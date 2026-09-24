@props(['story', 'primary' => false])
<article class="story-card {{ $primary ? 'story-card--primary' : 'story-card--supporting' }}" data-stagger-item>
    <div class="story-art"><img src="{{ $story->artwork_url }}" width="576" height="324" alt="" loading="lazy" decoding="async" @if($primary) data-parallax @endif></div>
    <div class="story-copy">
        @if($primary)<span class="story-featured-label">FEATURED</span>@endif
        <p class="story-category">{{ $story->category?->name ?? 'Stories' }}@if($story->tags->isNotEmpty()) <span>|</span> {{ $story->tags->first()->name }}@endif</p>
        <h3><a href="{{ route('public.content', $story->slug) }}">{{ $story->title }}</a></h3>
        @if($story->excerpt)<p class="story-excerpt">{{ $story->excerpt }}</p>@endif
        <a href="{{ route('public.content', $story->slug) }}" class="story-read {{ $primary ? 'story-read--primary' : '' }}" aria-label="Read story: {{ $story->title }}">Read Story <x-site-icon name="arrow" /></a>
        @if($primary)
            <div class="story-meta">
                @if($story->submittedBy)<span><span class="story-author-initial" aria-hidden="true">{{ mb_substr($story->submittedBy->name, 0, 1) }}</span> By {{ $story->submittedBy->name }}</span>@endif
                @if($story->published_at)<time datetime="{{ $story->published_at->toIso8601String() }}">{{ $story->published_at->format('M d, Y') }}</time>@endif
                <span><x-site-icon name="clock" /> {{ $story->reading_minutes }} min read</span>
            </div>
        @endif
    </div>
</article>
