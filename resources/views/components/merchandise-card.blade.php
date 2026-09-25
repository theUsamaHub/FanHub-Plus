@props(['item', 'saved' => false])
<article class="merch-card">
    <a class="merch-card__link" href="{{ route('public.merchandise', $item->slug) }}" aria-label="View details: {{ $item->name }}">
        <img class="merch-card__image" src="{{ $item->artwork_url }}" data-merch-image data-fallback="{{ asset(config('homepage.images.merchandise')) }}" alt="{{ $item->imageMedia?->alt_text ?: $item->name }}" width="480" height="560" loading="lazy">
        <div class="merch-card__copy">
            <p class="merch-card__category">{{ $item->category?->name }}</p>
            <h3>{{ $item->name }}</h3>
            <span class="merch-card__details">View Details <x-site-icon name="arrow" /></span>
        </div>
    </a>
    @if($item->display_tag)<span class="merch-tag merch-tag--{{ $item->tag }}">{{ $item->display_tag }}</span>@endif
    <x-merchandise-bookmark :item="$item" :saved="$saved" />
</article>
