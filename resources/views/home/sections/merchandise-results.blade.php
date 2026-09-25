<div class="merch-results" data-merch-results data-count="{{ $merchandise->count() }}" data-category="{{ $activeMerchFilter }}">
    @if($merchandise->isNotEmpty())
        <div class="swiper merch-swiper" aria-label="Merchandise collection" tabindex="0">
            <div class="swiper-wrapper">
                @foreach($merchandise as $item)
                    <div class="swiper-slide" data-merch-slide><x-merchandise-card :item="$item" :saved="in_array($item->id, $savedMerchandise)" /></div>
                @endforeach
            </div>
        </div>
    @else
        <p class="merch-empty">{{ $activeMerchFilter === 'all' ? 'New merchandise will appear here soon.' : 'No merchandise in this fandom yet. Explore another category.' }}</p>
    @endif
</div>
