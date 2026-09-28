<article class="fan-detail">
    <header><p class="fan-list__eyebrow">{{ $content->category?->name ?? 'Community' }} / {{ ucfirst($content->type) }}</p><h2>{{ $content->title }}</h2><p class="fan-detail__byline">By {{ $content->submittedBy?->profile?->display_name ?: ($content->submittedBy?->name ?? 'Community creator') }}@if($content->published_at) · <time datetime="{{ $content->published_at->toIso8601String() }}">{{ $content->published_at->format('M j, Y') }}</time>@endif</p></header>
    <img class="fan-detail__cover" src="{{ $content->artwork_url }}" alt="{{ $content->cover?->alt_text ?? '' }}" data-image-fallback="{{ asset(config('homepage.images.multimedia')) }}">
    @if($content->excerpt)<p class="fan-detail__lead">{{ $content->excerpt }}</p>@endif
    <div class="fan-detail__prose">{!! \App\Support\SafeArticleHtml::render($content->body) !!}</div>
    @foreach($content->mediaByRole('gallery')->filter(fn ($media) => $media->isImage() && $media->hasValidPath()) as $media)<img class="fan-detail__gallery" src="{{ $media->url }}" alt="{{ $media->alt_text ?? '' }}" loading="lazy">@endforeach
    @foreach($content->mediaByRole('trailer')->filter(fn ($media) => $media->isVideo() && $media->hasValidPath()) as $media)<video controls preload="metadata" src="{{ $media->url }}" aria-label="{{ $content->title }} video"></video>@endforeach
    @foreach($content->mediaByRole('audio_clip')->filter(fn ($media) => $media->isAudio() && $media->hasValidPath()) as $media)<audio controls preload="metadata" src="{{ $media->url }}" aria-label="{{ $content->title }} audio"></audio>@endforeach
</article>
