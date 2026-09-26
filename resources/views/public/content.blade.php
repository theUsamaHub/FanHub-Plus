@extends('layouts.public')
@section('title', $content->title.' | Fan Hub Plus')
@section('content')
<article class="fandom-reading fandom-page--{{ $content->category?->slug ?? 'all' }}">
    <header class="fandom-reading__header">
        <a class="fandom-back" href="{{ route('public.explore', array_filter(['category' => $content->category?->slug])) }}">← Explore {{ $content->category?->name ?? 'stories' }}</a>
        <p class="fandom-eyebrow">{{ $content->category?->name ?? 'Stories' }} / {{ ucfirst($content->type ?? 'article') }} @if($content->is_featured) / EDITOR'S PICK @endif</p>
        <h1>{{ $content->title }}</h1>
        <div class="fandom-reading__meta"><span>@if($content->submittedBy)By {{ $content->submittedBy->name }}@else FanHub Plus @endif</span><span>{{ $content->reading_minutes }} min read</span>@if($content->published_at)<time datetime="{{ $content->published_at->toIso8601String() }}">{{ $content->published_at->format('M d, Y') }}</time>@endif</div>
    </header>
    <figure class="fandom-reading__cover"><img src="{{ $content->artwork_url }}" width="1440" height="810" alt="{{ $content->cover?->alt_text ?? '' }}" fetchpriority="high" data-image-fallback="{{ asset(config('homepage.images.story')) }}"></figure>
    <div class="fandom-reading__layout">
        <aside class="fandom-reading__aside"><p class="fandom-eyebrow">IN THIS UNIVERSE</p><a href="{{ route('public.explore', array_filter(['category' => $content->category?->slug])) }}">{{ $content->category?->name ?? 'All stories' }} <x-site-icon name="arrow" /></a>@if($content->tags->isNotEmpty())<div class="fandom-reading__tags">@foreach($content->tags as $tag)<span>{{ $tag->name }}</span>@endforeach</div>@endif</aside>
        <div class="fandom-reading__prose">
            @if($content->excerpt)<p class="fandom-reading__lead">{{ $content->excerpt }}</p>@endif
            @if($content->release_date)<p class="fandom-reading__release">Release date: <time datetime="{{ $content->release_date->format('Y-m-d') }}">{{ $content->release_date->format('F j, Y') }}</time></p>@endif
            <div class="fandom-reading__body fandom-reading__body--rich">{!! \App\Support\SafeArticleHtml::render($content->body) !!}</div>
            @if($content->mediaByRole('gallery')->isNotEmpty())<div class="fandom-reading__gallery">@foreach($content->mediaByRole('gallery') as $media)<img src="{{ $media->url }}" alt="{{ $media->alt_text ?? '' }}" loading="lazy">@endforeach</div>@endif
            @foreach($content->mediaByRole('trailer') as $media)<video class="fandom-reading__media" controls preload="metadata" aria-label="{{ $content->title }} trailer" src="{{ $media->url }}"></video>@endforeach
            @foreach($content->mediaByRole('audio_clip') as $media)<audio class="fandom-reading__media" controls preload="metadata" aria-label="{{ $content->title }} audio" src="{{ $media->url }}"></audio>@endforeach
        </div>
    </div>
    @if($related->isNotEmpty())<section class="fandom-reading__related" aria-labelledby="related-title"><div class="fandom-section-title"><div><p class="fandom-eyebrow">KEEP EXPLORING</p><h2 id="related-title">One more <em>chapter.</em></h2></div></div><div class="fandom-story-grid">@foreach($related as $story)<x-fandom-story-card :story="$story" />@endforeach</div></section>@endif
    <x-member-interactions :item="$content" type="content" />
</article>
@endsection
