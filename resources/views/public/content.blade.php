@extends('layouts.public')
@section('title', $content->title.' | Fan Hub Plus')
@section('main-class', 'content-detail-main')
@push('styles')
    @vite('resources/js/pages/content-detail.js')
@endpush
@section('content')
<article class="content-detail" data-content-detail>
    <nav class="cd-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a><span>/</span>
        <a href="{{ route('public.explore', array_filter(['category' => $content->category?->slug])) }}">{{ $content->category?->name ?? 'Explore' }}</a><span>/</span>
        <span aria-current="page">{{ $content->title }}</span>
    </nav>
    @if(session('success'))<p class="cd-notice" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="cd-notice" role="alert">{{ $errors->first() }}</p>@endif
    <div class="cd-hero">
        <figure class="cd-poster"><img src="{{ $content->artwork_url }}" alt="{{ $content->cover?->alt_text ?: $content->title }}" fetchpriority="high" width="560" height="620" data-image-fallback="{{ asset(config('homepage.images.story')) }}"></figure>
        <header class="cd-intro">
            <div class="cd-tags"><span class="cd-tag cd-tag--category">{{ $content->category?->name ?? 'Content' }}</span>@foreach($content->tags as $tag)<a class="cd-tag" href="{{ route('public.explore', ['tag' => $tag->id, 'category' => $content->category?->slug]) }}">{{ $tag->name }}</a>@endforeach</div>
            @php($titleWords = preg_split('/\s+/u', trim($content->title)))
            <h1>@if(count($titleWords) > 1){{ implode(' ', array_slice($titleWords, 0, -1)) }} <span class="cd-title-accent">{{ end($titleWords) }}</span>@else{{ $content->title }}@endif</h1>
            <div class="cd-meta">
                @if($content->release_date)<time datetime="{{ $content->release_date->format('Y-m-d') }}">{{ $content->release_date->format('Y') }}</time>@endif
                <span>{{ $content->kind_label }}</span>
                @if($content->release_label)<span>{{ $content->release_label }}</span>@endif
                @if($content->release_date?->isFuture())<span>Upcoming</span>@endif
            </div>
            @if($content->excerpt)<p class="cd-synopsis">{{ $content->excerpt }}</p>@elseif($content->body)<p class="cd-synopsis">{{ Str::limit(html_entity_decode(strip_tags($content->body)), 400) }}</p>@endif
            <div class="cd-actions">
                @auth
                    <form method="post" action="{{ route('user.bookmark', ['content', $content->id]) }}">@csrf<input type="hidden" name="saved" value="{{ $saved ? 0 : 1 }}"><button class="cd-button" aria-pressed="{{ $saved ? 'true' : 'false' }}"><i class="bi bi-bookmark{{ $saved ? '-fill' : '' }}" aria-hidden="true"></i>{{ $saved ? 'Saved to Favorites' : 'Add to Favorites' }}</button></form>
                @else
                    <a href="{{ route('login') }}" class="cd-button"><i class="bi bi-bookmark" aria-hidden="true"></i>Add to Favorites</a>
                @endauth
                <button class="cd-button cd-button--share" type="button" data-share-url="{{ url()->current() }}" data-share-title="{{ $content->title }}" aria-label="Share {{ $content->title }}"><i class="bi bi-share" aria-hidden="true"></i></button>
                <span class="cd-share-status" data-share-status role="status"></span>
            </div>
        </header>
        <div class="cd-media">
            @forelse($trailers as $trailer)
                <div class="cd-trailer" data-trailer>
                    <video controls playsinline preload="none" poster="{{ $gallery->first()?->url ?? $content->artwork_url }}" aria-label="{{ $content->title }} trailer" src="{{ $trailer->url }}"></video>
                    <button type="button" class="cd-trailer-cover" data-trailer-play aria-label="Play {{ $content->title }} trailer" hidden>
                        <span class="cd-trailer-label">Official Trailer</span>
                        @if($trailer->duration)<span class="cd-duration">{{ gmdate('i:s', (int) $trailer->duration) }}</span>@endif
                        <span class="cd-play"><i class="bi bi-play-fill" aria-hidden="true"></i></span>
                        <span class="cd-trailer-title"><strong>{{ $content->title }}</strong><span>Official Trailer</span></span>
                    </button>
                </div>
            @empty
                <div class="cd-trailer cd-trailer--empty"><img src="{{ $gallery->first()?->url ?? $content->artwork_url }}" alt="" loading="lazy"><span>Trailer coming soon</span></div>
            @endforelse
            @foreach($audioClips as $audio)
                <div class="cd-audio" data-audio-player>
                    <img src="{{ $content->artwork_url }}" alt="" loading="lazy">
                    <div class="cd-audio-body"><strong>{{ $audio->alt_text ?: pathinfo($audio->original_filename, PATHINFO_FILENAME) }}</strong><span>{{ $content->title }}</span>
                        <audio controls preload="metadata" src="{{ $audio->url }}" aria-label="{{ $audio->alt_text ?: $content->title.' audio' }}"></audio>
                        <div class="cd-audio-controls" hidden><button type="button" class="cd-audio-play" aria-label="Play audio" data-audio-toggle><i class="bi bi-play-fill" aria-hidden="true"></i></button><input type="range" min="0" max="100" value="0" step="0.1" aria-label="Audio position" data-audio-seek disabled><span data-audio-time>0:00 / {{ $audio->duration ? gmdate('i:s', (int) $audio->duration) : '0:00' }}</span></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="cd-middle">
        <section class="cd-gallery-section" aria-labelledby="cd-gallery-title">
            <h2 class="cd-heading" id="cd-gallery-title"><i class="bi bi-images" aria-hidden="true"></i>Image Gallery</h2>
            @if($gallery->isNotEmpty())
                <div class="cd-gallery-wrap">
                    <div class="swiper cd-gallery" data-gallery-slider><div class="swiper-wrapper">
                        @foreach($gallery as $media)<a class="swiper-slide cd-gallery-image" href="{{ $media->url }}" data-gallery-image aria-label="Open image {{ $loop->iteration }}: {{ $media->alt_text ?: $content->title }}"><img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $content->title.' — image '.$loop->iteration }}" loading="lazy" width="360" height="200"></a>@endforeach
                    </div></div>
                    @if($gallery->count() > 1)<button class="cd-arrow cd-arrow--prev" data-gallery-prev aria-label="Previous gallery images" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button class="cd-arrow cd-arrow--next" data-gallery-next aria-label="Next gallery images" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button><div class="cd-pagination" data-gallery-pagination></div>@endif
                </div>
            @else<p class="cd-empty">Gallery images haven’t been added yet.</p>@endif
        </section>
        <aside class="cd-feature" aria-labelledby="cd-feature-title">
            <h2 class="cd-heading" id="cd-feature-title"><i class="bi bi-file-earmark-richtext-fill" aria-hidden="true"></i>{{ $attachments->isNotEmpty() ? 'Featured Article' : 'About this Content' }}</h2>
            <div class="cd-feature-card">
                <img src="{{ $gallery->last()?->url ?? $content->artwork_url }}" alt="" loading="lazy">
                <div><span class="cd-tag cd-tag--category">{{ $attachments->isNotEmpty() ? 'Read & explore' : ($content->category?->name ?? 'Explore') }}</span>
                    <h3>{{ $attachments->first()?->alt_text ?: $content->title }}</h3>
                    <p>{{ Str::limit($content->excerpt ?: strip_tags($content->body ?? ''), 115) }}</p>
                    @if($attachments->isNotEmpty())
                        @foreach($attachments as $attachment)<a class="cd-download" href="{{ $attachment->url }}" download><i class="bi bi-download" aria-hidden="true"></i>{{ $attachment->mime_type === 'application/pdf' ? 'Download Article (PDF)' : 'Download document' }}<small>{{ $attachment->size_formatted }}</small></a>@endforeach
                    @elseif($content->body)<a class="cd-read" href="#cd-story">Read more <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endif
                </div>
            </div>
        </aside>
    </div>
    <section class="cd-characters" aria-labelledby="cd-characters-title">
        <h2 class="cd-heading" id="cd-characters-title"><i class="bi bi-people-fill" aria-hidden="true"></i>Main Characters</h2>
        @if($characters->isNotEmpty())
            <div class="cd-character-stage">
                <div class="swiper cd-character-slider" data-character-coverflow><div class="swiper-wrapper">
                    @foreach($characters as $character)
                        <a class="swiper-slide cd-character" href="{{ route('public.character', $character->slug) }}"><img src="{{ $character->artwork_url }}" alt="{{ $character->imageMedia?->alt_text ?: $character->name }}" loading="lazy" width="380" height="520" data-image-fallback="{{ asset(config('homepage.images.character')) }}"><span class="cd-character-caption"><strong>{{ $character->name }}</strong><span>{{ Str::limit(strip_tags($character->bio ?? ''), 65) }}</span></span></a>
                    @endforeach
                </div></div>
                @if($characters->count() > 1)<button class="cd-arrow cd-arrow--prev" data-character-prev aria-label="Previous character" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button class="cd-arrow cd-arrow--next" data-character-next aria-label="Next character" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>@endif
            </div>
        @else<p class="cd-empty">Characters haven’t been added to this content yet.</p>@endif
    </section>
    <section class="cd-merchandise" aria-labelledby="cd-merch-title">
        <div class="cd-section-header"><h2 class="cd-heading" id="cd-merch-title"><i class="bi bi-shop" aria-hidden="true"></i>Merchandise</h2>@if($merchandise->isNotEmpty())<a class="cd-view-all" href="{{ route('public.section', ['section' => 'merchandise', 'content' => $content->id]) }}">View All <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endif @if($merchandise->count() > 6)<div class="cd-inline-arrows"><button class="cd-arrow" data-merch-prev aria-label="Previous merchandise" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button class="cd-arrow" data-merch-next aria-label="Next merchandise" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button></div>@endif</div>
        @if($merchandise->isNotEmpty())<div class="swiper cd-merch-slider" data-merch-slider><div class="swiper-wrapper">
            @foreach($merchandise as $item)<div class="swiper-slide cd-merch"><a href="{{ route('public.merchandise', $item->slug) }}"><img src="{{ $item->artwork_url }}" alt="{{ $item->imageMedia?->alt_text ?: $item->name }}" loading="lazy" width="320" height="240" data-image-fallback="{{ asset(config('homepage.images.merchandise')) }}"><span><strong>{{ $item->name }}</strong><small>{{ $item->display_tag ?: ($item->character?->name ?? $content->category?->name) }}</small></span></a><x-merchandise-bookmark :item="$item" :saved="in_array($item->id, $savedMerchandise)" /></div>@endforeach
        </div></div>@else<p class="cd-empty">No merchandise has been linked yet.</p>@endif
    </section>
    @if($content->body)<section class="cd-story" id="cd-story" aria-labelledby="cd-story-title"><h2 class="cd-heading" id="cd-story-title"><i class="bi bi-book" aria-hidden="true"></i>About {{ $content->title }}</h2><div class="cd-prose">{!! \App\Support\SafeArticleHtml::render($content->body) !!}</div></section>@endif
    @if($events->isNotEmpty())<section class="cd-events" aria-labelledby="cd-events-title"><h2 class="cd-heading" id="cd-events-title"><i class="bi bi-calendar-event" aria-hidden="true"></i>Upcoming Events</h2><div class="cd-related-grid">@foreach($events as $event)<a class="cd-related-card" href="{{ route('events.show', $event->slug) }}"><img src="{{ $event->artwork_url }}" alt="" loading="lazy"><span><strong>{{ $event->title }}</strong><small>{{ $event->start_at->format('M j, Y') }} · {{ $event->city }}</small></span></a>@endforeach</div></section>@endif
    @if($related->isNotEmpty())<section class="cd-related" aria-labelledby="cd-related-title"><h2 class="cd-heading" id="cd-related-title"><i class="bi bi-collection" aria-hidden="true"></i>More in {{ $content->category?->name ?? 'this fandom' }}</h2><div class="cd-related-grid">@foreach($related as $item)<a class="cd-related-card" href="{{ route('public.content', $item->slug) }}"><img src="{{ $item->artwork_url }}" alt="" loading="lazy"><span><strong>{{ $item->title }}</strong><small>{{ Str::limit($item->excerpt, 90) }}</small></span></a>@endforeach</div></section>@endif
    <dialog class="cd-lightbox" data-gallery-dialog aria-label="Gallery image viewer"><button type="button" class="cd-lightbox-close" aria-label="Close image"><i class="bi bi-x-lg" aria-hidden="true"></i></button><figure><img alt="" data-lightbox-image><figcaption data-lightbox-caption></figcaption></figure><span class="cd-lightbox-hint">Click anywhere or press Esc to close</span></dialog>
</article>
@endsection
