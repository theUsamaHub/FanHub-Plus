@extends('layouts.public')
@section('title', $merchandise->name.' | Fan Hub Plus')
@section('content')
<article class="fh-content-page merch-detail">
    <a href="{{ route('public.section', 'merchandise') }}">← All merchandise</a>
    <div class="merch-detail__main">
        <img class="merch-detail__image" src="{{ $merchandise->artwork_url }}" data-merch-image data-fallback="{{ asset(config('homepage.images.merchandise')) }}" width="640" height="740" alt="{{ $merchandise->imageMedia?->alt_text ?: $merchandise->name }}">
        <div>
            <p class="merch-detail__category">{{ $merchandise->category?->name }}</p>
            <h1>{{ $merchandise->name }}</h1>
            @if($merchandise->display_tag)<span class="merch-tag merch-tag--{{ $merchandise->tag }}">{{ $merchandise->display_tag }}</span>@endif
            <p class="merch-detail__description">{{ $merchandise->description ?: 'More details about this item are on the way.' }}</p>
            <p>Status: {{ $merchandise->is_upcoming ? 'Upcoming' : 'Released' }}</p>
            @if($merchandise->release_date)<p>Release date: {{ $merchandise->release_date->format('F j, Y') }}</p>
            @elseif($merchandise->is_upcoming)<p>Release date to be announced.</p>@endif
            <div class="merch-detail__save"><x-merchandise-bookmark :item="$merchandise" :saved="in_array($merchandise->id, $savedMerchandise)" /><span>Save to your bookmarks</span></div>
        </div>
    </div>
    @if($related->isNotEmpty())
        <h2>More from {{ $merchandise->category?->name }}</h2>
        <div class="merch-grid">@foreach($related as $item)<x-merchandise-card :item="$item" :saved="in_array($item->id, $savedMerchandise)" />@endforeach</div>
    @endif
    <x-member-interactions :item="$merchandise" type="merchandise" />
</article>
@endsection
