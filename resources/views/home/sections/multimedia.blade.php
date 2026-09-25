@php
    $mediaFallback = asset(config('homepage.images.multimedia'));
    $mediaRows = $multimediaItems->isEmpty() ? collect() : $multimediaItems->chunk((int) ceil($multimediaItems->count() / 2))->values();
    if ($mediaRows->count() === 1) $mediaRows->push($mediaRows->first());
@endphp
<section id="multimedia" class="home-section home-multimedia" data-home-multimedia aria-labelledby="home-multimedia-title">
    <header class="home-section-heading home-multimedia__heading">
        <p class="home-multimedia__eyebrow"><span></span>Visual universe<span></span></p>
        <h2 id="home-multimedia-title">Multi<span>media</span></h2>
        <p class="home-section-subtitle">Wallpapers, fan art, screenshots, and more from every universe.</p>
    </header>
    @if($multimediaItems->isNotEmpty())
        <div class="home-multimedia__rows">
            @foreach($mediaRows as $row)
                <div class="home-multimedia__row {{ $loop->last ? 'home-multimedia__row--reverse' : '' }}" data-media-row aria-label="{{ $loop->first ? 'First' : 'Second' }} multimedia row">
                    <div class="home-multimedia__track" data-media-track>
                        <div class="home-multimedia__group" data-media-group>
                            @foreach($row as $item)
                                @php
                                    $imageMedia = $item->media->first(fn ($media) => $media->isImage() && $media->hasValidPath());
                                    $mediaImage = $imageMedia?->url ?: $mediaFallback;
                                    $mediaIcon = match ($item->type) { 'video' => 'bi-play-circle', 'audio' => 'bi-music-note-beamed', default => 'bi-images' };
                                @endphp
                                <a class="home-multimedia__card" href="{{ route('public.content', $item->slug) }}" aria-label="{{ $item->title }}">
                                    <img src="{{ $mediaImage }}" data-image-fallback="{{ $mediaFallback }}" alt="{{ $imageMedia?->alt_text ?: $item->title }}" width="256" height="144" loading="lazy" decoding="async">
                                    <span class="home-multimedia__caption"><i class="bi {{ $mediaIcon }}" aria-hidden="true"></i><strong>{{ $item->title }}</strong>@if($item->category)<small>{{ $item->category->name }}</small>@endif</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="home-multimedia__footer">
            <span>Discover a different side of your fandom.</span>
            <button type="button" data-media-pause aria-pressed="false"><i class="bi bi-pause" aria-hidden="true"></i><span>Pause motion</span></button>
        </div>
    @else
        <p class="home-empty">Fan art, videos, and more will appear here when published.</p>
    @endif
</section>
