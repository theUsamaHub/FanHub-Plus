@props(['item', 'saved' => false, 'catalog' => false, 'listView' => false])
<article @class(['merch-card', 'merch-card--catalog' => $catalog, 'merch-card--list' => $listView])>
    <a class="merch-card__link" href="{{ route('public.merchandise', $item->slug) }}" aria-label="View details: {{ $item->name }}">
        <div class="merch-card__image-wrap">
            <img class="merch-card__image" src="{{ $item->artwork_url }}" data-merch-image data-fallback="{{ asset(config('homepage.images.merchandise')) }}" alt="{{ $item->imageMedia?->alt_text ?: $item->name }}" width="480" height="560" loading="lazy">
        </div>
        <div class="merch-card__copy">
            <div class="merch-card__meta">
                <p class="merch-card__category">{{ $item->category?->name }}</p>
                @if($item->display_tag)<span class="merch-tag merch-tag--{{ $item->tag }}">{{ $item->display_tag }}</span>@endif
            </div>
            <h3>{{ $item->name }}</h3>
            @if($catalog)
                <p class="merch-card__status">{{ $item->is_upcoming ? 'Upcoming' : 'Released' }}@if($item->release_date) · {{ $item->release_date->format('M j, Y') }}@endif</p>
            @endif
            <div class="merch-card__footer">
                <span class="merch-card__details">View Details <x-site-icon name="arrow" /></span>
                @if($listView)
                    <span class="merch-card__wishlist">
                        <x-merchandise-bookmark :item="$item" :saved="$saved" />
                    </span>
                @endif
            </div>
        </div>
    </a>
    @if(!$listView && $item->display_tag)<span class="merch-tag merch-tag--{{ $item->tag }}">{{ $item->display_tag }}</span>@endif
    @if(!$listView)
        <x-merchandise-bookmark :item="$item" :saved="$saved" />
    @endif
</article>
