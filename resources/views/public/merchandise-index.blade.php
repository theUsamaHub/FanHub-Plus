@extends('layouts.public')
@section('title', 'Official Merchandise | Fan Hub Plus')
@section('content')
<section class="fh-content-page merch-catalog">
    <header class="merch-catalog__header">
        <p class="merch-eyebrow">THE FAN COLLECTION</p>
        <h1>Find your next <span class="fan-gradient-text">favorite.</span></h1>
        <p>Explore merchandise from the worlds you love. Discover collectibles, limited editions, and upcoming releases.</p>
    </header>
    @if($errors->any())
        <div class="merch-empty" role="alert"><p>{{ $errors->first() }}</p><a href="{{ route('public.section', 'merchandise') }}">Reset filters</a></div>
    @endif
    <form method="get" action="{{ route('public.section', 'merchandise') }}" class="merch-catalog__form" role="search" aria-label="Search merchandise">
        <div class="merch-catalog__search">
            <label for="merch-search">Search merchandise
                <input id="merch-search" type="search" name="q" value="{{ $filters['q'] }}" maxlength="120" placeholder="Search by name…">
            </label>
            <label for="merch-sort">Sort by
                <select id="merch-sort" name="sort">
                    @foreach(['newest' => 'Newest arrivals', 'popular' => 'Most viewed', 'name' => 'Name: A–Z'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="fh-button" type="submit">Search</button>
        </div>
        <details class="merch-catalog__filters" @if($hasFilters) open @endif>
            <summary>Refine your collection <span>{{ $hasFilters ? 'Filters active' : 'Category, release status & edition' }}</span></summary>
            <div class="merch-catalog__filter-fields">
                <label for="merch-category">Category
                    <select id="merch-category" name="category">
                        <option value="all">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                        @if($filters['category'] !== 'all' && !$categories->contains('slug', $filters['category']))
                            <option value="{{ $filters['category'] }}" selected>{{ config('fandoms.'.$filters['category'].'.name') }}</option>
                        @endif
                    </select>
                </label>
                <label for="merch-status">Release status
                    <select id="merch-status" name="status">
                        <option value="">All releases</option>
                        <option value="released" @selected($filters['status'] === 'released')>Released</option>
                        <option value="upcoming" @selected($filters['status'] === 'upcoming')>Upcoming</option>
                    </select>
                </label>
                <label for="merch-tag">Edition
                    <select id="merch-tag" name="tag">
                        <option value="">All editions</option>
                        @foreach(['limited_edition' => 'Limited Edition', 'pre_order' => 'Pre-Order', 'collectible' => 'Collectible', 'standard' => 'Standard'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['tag'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="fh-button" type="submit">Apply filters</button>
            </div>
        </details>
    </form>
    <div class="merch-catalog__results-heading">
        <h2>Merchandise <span>{{ number_format($items->total()) }} {{ Str::plural('item', $items->total()) }}</span></h2>
        @if($hasFilters)<a href="{{ route('public.section', 'merchandise') }}">Clear filters ×</a>@endif
    </div>
    <div class="merch-grid">
        @forelse($items as $item)
            <x-merchandise-card :item="$item" :saved="in_array($item->id, $savedMerchandise)" :catalog="true" />
        @empty
            <div class="merch-empty">
                <h2>{{ $hasFilters ? 'No merchandise matches your selected filters.' : 'No merchandise is currently available.' }}</h2>
                <p>{{ $hasFilters ? 'Try a different name or broaden your filters to discover more.' : 'Check back soon for new additions to the collection.' }}</p>
                @if($hasFilters)<a class="fh-button" href="{{ route('public.section', 'merchandise') }}">Browse all merchandise</a>@endif
            </div>
        @endforelse
    </div>
    @if($items->count())
        <p class="merch-pagination-summary">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ number_format($items->total()) }} items</p>
    @endif
    {{ $items->onEachSide(1)->links('public.partials.merchandise-pagination') }}
</section>
@endsection
