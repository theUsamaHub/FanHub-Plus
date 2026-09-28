@props(['content'])
<article class="fan-card">
    <a href="{{ route('public.fan-content.show', $content->slug) }}" data-fan-open aria-haspopup="dialog">
        <div class="fan-card__art"><img src="{{ $content->artwork_url }}" alt="" width="720" height="480" loading="lazy" data-image-fallback="{{ asset(config('homepage.images.multimedia')) }}"><span>{{ ucfirst($content->type) }}</span><i class="bi bi-arrows-angle-expand" aria-hidden="true"></i></div>
        <div class="fan-card__body"><p>{{ $content->category?->name ?? 'Community' }}</p><h3>{{ $content->title }}</h3>@if($content->excerpt)<p class="fan-card__excerpt">{{ Str::limit(strip_tags($content->excerpt), 130) }}</p>@endif<div class="fan-card__author"><span>{{ $content->submittedBy?->profile?->display_name ?: ($content->submittedBy?->name ?? 'Community creator') }}</span><span>View creation <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span></div></div>
    </a>
</article>
