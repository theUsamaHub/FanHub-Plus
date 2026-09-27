@extends('layouts.public')
@section('title', $merchandise->name.' | Fan Hub Plus')
@section('content')
<article class="fh-content-page merch-catalog merch-product">
    <nav class="merch-product__breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('public.section', 'merchandise') }}">← All merchandise</a>
        @if($merchandise->category)
            <span aria-hidden="true">/</span>
            <a href="{{ route('public.section', ['section' => 'merchandise', 'category' => $merchandise->category->slug]) }}">{{ $merchandise->category->name }}</a>
        @endif
    </nav>
    <div class="merch-product__main">
        <div class="merch-product__artwork">
            <img src="{{ $merchandise->artwork_url }}" data-merch-image data-fallback="{{ asset(config('homepage.images.merchandise')) }}" width="640" height="640" alt="{{ $merchandise->imageMedia?->alt_text ?: $merchandise->name }}" fetchpriority="high">
        </div>
        <div class="merch-product__info">
            <p class="merch-eyebrow">{{ $merchandise->category?->name ?: 'THE FAN COLLECTION' }}</p>
            <h1>{{ $merchandise->name }}</h1>
            @if($merchandise->display_tag)<span class="merch-tag merch-tag--{{ $merchandise->tag }}">{{ $merchandise->display_tag }}</span>@endif
            <p class="merch-product__status">Status: {{ $merchandise->is_upcoming ? 'Upcoming' : 'Released' }}</p>
            <p class="merch-product__release">
                @if($merchandise->release_date)Release date: {{ $merchandise->release_date->format('F j, Y') }}
                @elseif($merchandise->is_upcoming)Release date to be announced.
                @else Release date not provided.
                @endif
            </p>
            @if($merchandise->content || $merchandise->character)
                <dl class="merch-product__facts">
                    @if($merchandise->content)<div><dt>From the world of</dt><dd><a href="{{ route('public.content', $merchandise->content->slug) }}">{{ $merchandise->content->title }} ↗</a></dd></div>@endif
                    @if($merchandise->character)<div><dt>Character</dt><dd><a href="{{ route('public.character', $merchandise->character->slug) }}">{{ $merchandise->character->name }} ↗</a></dd></div>@endif
                </dl>
            @endif
            <div class="merch-product__save">
                <x-merchandise-bookmark :item="$merchandise" :saved="in_array($merchandise->id, $savedMerchandise)" />
                <div><strong>Keep it on your wishlist</strong><p>Save this item to your bookmarks.</p></div>
            </div>
        </div>
    </div>
    <section class="merch-product__description" aria-labelledby="merch-description">
        <p class="merch-eyebrow">A CLOSER LOOK</p>
        <h2 id="merch-description">About this item</h2>
        <p>{{ $merchandise->description ?: 'More details about this item are on the way.' }}</p>
    </section>
    @if($related->isNotEmpty())
        <section class="merch-product__related" aria-labelledby="merch-related">
            <div class="merch-catalog__results-heading"><h2 id="merch-related">More to discover</h2><a href="{{ route('public.section', 'merchandise') }}">View all →</a></div>
            <div class="merch-grid">@foreach($related as $item)<x-merchandise-card :item="$item" :saved="in_array($item->id, $savedMerchandise)" :catalog="true" />@endforeach</div>
        </section>
    @endif
    <x-member-interactions :item="$merchandise" type="merchandise" />
</article>
@endsection
