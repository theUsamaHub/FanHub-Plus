@extends('layouts.public')
@section('title', $fandom['name'].' Fandom | Fan Hub Plus')
@section('main-class', 'fandom-landing-page')
@push('styles')
    @vite('resources/css/pages/fandom-landing.css')
@endpush
@section('content')
<div class="fandom-landing" data-fandom-landing data-fandom="{{ $fandom['slug'] }}">
    <!-- Hero Section -->
    <section class="fandom-hero" data-fandom-hero aria-labelledby="fandom-title">
        <div class="fandom-hero__bg" aria-hidden="true">
            <div class="fandom-hero__gradient" style="background: {{ $fandom['gradient'] }};"></div>
            <div class="fandom-hero__particles" data-fandom-particles style="--particle-colors: {{ implode(',', $fandom['particles']) }};"></div>
            @if($category->iconMedia && $category->iconMedia->isImage())
                <img src="{{ $category->iconMedia->url }}" alt="" class="fandom-hero__art" aria-hidden="true" fetchpriority="high">
            @else
                <div class="fandom-hero__artwork" style="background: {{ $fandom['gradient'] }};">
                    <x-site-icon :name="$fandom['icon']" class="fandom-hero__icon-main" />
                </div>
            @endif
            <div class="fandom-hero__vignette" aria-hidden="true"></div>
        </div>
        <div class="fandom-hero__content" data-animate="fade-up">
            <div class="fandom-hero__badge" data-animate="fade-up" data-delay="100">
                <x-site-icon :name="$fandom['icon']" class="fandom-hero__icon" />
                <span>{{ $fandom['name'] }} Fandom</span>
            </div>
            <h1 id="fandom-title" data-animate="fade-up" data-delay="200">{{ $fandom['name'] }}</h1>
            <p class="fandom-hero__tagline" data-animate="fade-up" data-delay="300">{{ $fandom['tagline'] }}</p>
            <p class="fandom-hero__desc" data-animate="fade-up" data-delay="400">{{ $fandom['description'] }}</p>
            
            <!-- Hero Stats -->
            <div class="fandom-hero__stats" data-animate="fade-up" data-delay="500" role="list" aria-label="{{ $fandom['name'] }} statistics">
                @foreach($fandom['stats'] as $key => $value)
                    <div class="fandom-stat" role="listitem">
                        <span class="fandom-stat__value" data-count="{{ (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT) }}">0</span>
                        <span class="fandom-stat__label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                    </div>
                @endforeach
                <div class="fandom-stat" role="listitem">
                    <span class="fandom-stat__value" data-count="{{ $stats['total_content'] }}">0</span>
                    <span class="fandom-stat__label">Total Content</span>
                </div>
                <div class="fandom-stat" role="listitem">
                    <span class="fandom-stat__value" data-count="{{ $stats['total_views'] }}">0</span>
                    <span class="fandom-stat__label">Total Views</span>
                </div>
                <div class="fandom-stat" role="listitem">
                    <span class="fandom-stat__value" data-count="{{ $stats['total_creators'] }}">0</span>
                    <span class="fandom-stat__label">Creators</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="fandom-hero__actions" data-animate="fade-up" data-delay="600">
                <a href="#fandom-content" class="fandom-btn fandom-btn--primary" data-magnetic>
                    <span>Explore Content</span>
                    <x-site-icon name="arrow" />
                </a>
                <a href="{{ route('user.submissions.create', ['category' => $fandom['slug']]) }}" class="fandom-btn fandom-btn--secondary" data-magnetic>
                    <span>Create Content</span>
                    <x-site-icon name="plus" />
                </a>
            </div>
        </div>
        <div class="fandom-hero__scroll" data-animate="fade-up" data-delay="800" aria-hidden="true">
            <x-site-icon name="arrow" class="fandom-scroll-icon" />
            <span>Discover</span>
        </div>
    </section>

    <!-- Featured Section -->
    @if($featured->isNotEmpty())
    <section class="fandom-featured" id="fandom-featured" aria-labelledby="featured-title">
        <div class="fandom-container">
            <header class="fandom-section-header" data-animate="fade-up">
                <div>
                    <p class="fandom-eyebrow">EDITOR'S PICKS</p>
                    <h2 id="featured-title">Featured <span>Content</span></h2>
                    <p>Hand-picked by our community curators</p>
                </div>
            </header>
            <div class="fandom-featured__grid" data-featured-grid>
                @foreach($featured as $index => $item)
                    <x-fandom-content-card :content="$item" data-animate="scale-up" data-delay="{{ $index * 100 }}" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Trending Section -->
    @if($trending->isNotEmpty())
    <section class="fandom-trending" id="fandom-trending" aria-labelledby="trending-title">
        <div class="fandom-container">
            <header class="fandom-section-header" data-animate="fade-up">
                <div>
                    <p class="fandom-eyebrow">TRENDING NOW</p>
                    <h2 id="trending-title">What's <span>Hot</span></h2>
                    <p>Content gaining traction in the community</p>
                </div>
            </header>
            <div class="fandom-trending__grid" data-trending-grid>
                @foreach($trending as $index => $item)
                    <x-fandom-content-card :content="$item" data-trending-card data-animate="fade-up" data-delay="{{ $index * 80 }}" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Main Content Grid with Filters -->
    <section class="fandom-content-section" id="fandom-content" aria-labelledby="content-title">
        <div class="fandom-container">
            <header class="fandom-section-header" data-animate="fade-up">
                <div>
                    <p class="fandom-eyebrow">ALL CONTENT</p>
                    <h2 id="content-title">Browse <span>{{ $fandom['name'] }}</span></h2>
                    <p>{{ $contents->total() }} stories and counting</p>
                </div>
            </header>

            <!-- Filter Bar -->
            <div class="fandom-filter-bar" data-animate="slide-up" data-delay="100">
                <form action="{{ route('public.fandom', $fandom['slug']) }}" method="GET" class="fandom-filter-form" role="search">
                    <div class="fandom-filter__row">
                        <div class="fandom-filter__field">
                            <label for="filter-search" class="fandom-filter__label">
                                <x-site-icon name="search" />
                                <span>Search</span>
                            </label>
                            <input type="search" 
                                   id="filter-search" 
                                   name="q" 
                                   value="{{ $filters['q'] ?? '' }}"
                                   placeholder="Search {{ strtolower($fandom['name']) }} content..."
                                   autocomplete="off">
                         </div>
                        <div class="fandom-filter__field fandom-filter__field--narrow">
                            <label for="filter-sort" class="fandom-filter__label">
                                <x-site-icon name="filter" />
                                <span>Sort</span>
                            </label>
                            <select id="filter-sort" name="sort">
                                <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Latest</option>
                                <option value="popular" {{ ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' }}>Popular</option>
                                <option value="trending" {{ ($filters['sort'] ?? '') === 'trending' ? 'selected' : '' }}>Trending</option>
                                <option value="alphabetical" {{ ($filters['sort'] ?? '') === 'alphabetical' ? 'selected' : '' }}>A-Z</option>
                            </select>
                        </div>
                        <div class="fandom-filter__field fandom-filter__field--narrow">
                            <label for="filter-type" class="fandom-filter__label">
                                <x-site-icon name="file-text" />
                                <span>Type</span>
                            </label>
                            <select id="filter-type" name="type">
                                <option value="">All Types</option>
                                <option value="article" {{ ($filters['type'] ?? '') === 'article' ? 'selected' : '' }}>Articles</option>
                                <option value="video" {{ ($filters['type'] ?? '') === 'video' ? 'selected' : '' }}>Videos</option>
                                <option value="audio" {{ ($filters['type'] ?? '') === 'audio' ? 'selected' : '' }}>Audio</option>
                                <option value="image" {{ ($filters['type'] ?? '') === 'image' ? 'selected' : '' }}>Images</option>
                            </select>
                        </div>
                        <button type="submit" class="fandom-filter__submit">
                            <span>Filter</span>
                            <x-site-icon name="search" />
                        </button>
                    </div>
                    
                    <!-- Advanced Filters Accordion -->
                    <details class="fandom-advanced-filters">
                        <summary class="fandom-advanced-filters__summary">
                            <span>Advanced Filters</span>
                            <x-site-icon name="chevron-down" class="fandom-advanced-filters__icon" />
                        </summary>
                        <div class="fandom-advanced-filters__content">
                            <div class="fandom-advanced-filters__grid">
                                <div class="fandom-filter__field">
                                    <label for="filter-tag" class="fandom-filter__label">
                                        <x-site-icon name="hash" />
                                        <span>Genre / Tag</span>
                                    </label>
                                    <select id="filter-tag" name="tag">
                                        <option value="">All Tags</option>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}" {{ ($filters['tag'] ?? '') == $tag->id ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="fandom-filter__field">
                                    <label for="filter-year" class="fandom-filter__label">
                                        <x-site-icon name="calendar" />
                                        <span>Year</span>
                                    </label>
                                    <input type="number" 
                                           id="filter-year" 
                                           name="year" 
                                           min="1900" 
                                           max="{{ date('Y') + 1 }}" 
                                           value="{{ $filters['year'] ?? '' }}" 
                                           placeholder="Any year">
                                </div>
                            </div>
                            <button type="submit" class="fandom-advanced-filters__apply">
                                <span>Apply Filters</span>
                                <x-site-icon name="arrow" />
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <!-- Content Grid -->
            <div class="fandom-content-grid" data-content-grid>
                @forelse($contents as $index => $content)
                    <x-fandom-content-card :content="$content" data-animate="scale-up" data-delay="{{ min($index * 60, 300) }}" />
                @empty
                    <div class="fandom-empty" data-animate="fade-up">
                        <x-site-icon name="search-x" class="fandom-empty__icon" />
                        <h3>No content found</h3>
                        <p>Try adjusting your filters or search terms</p>
                        <a href="{{ route('public.fandom', $fandom['slug']) }}" class="fandom-btn fandom-btn--ghost">Clear Filters</a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($contents->hasPages())
            <nav class="fandom-pagination" data-animate="fade-up" aria-label="Pagination">
                {{ $contents->links('vendor.pagination.fandom') }}
            </nav>
            @endif
        </div>
    </section>

    <!-- Tags Cloud Section -->
    @if($tags->isNotEmpty())
    <section class="fandom-tags" id="fandom-tags" aria-labelledby="tags-title">
        <div class="fandom-container">
            <header class="fandom-section-header" data-animate="fade-up">
                <div>
                    <p class="fandom-eyebrow">EXPLORE TOPICS</p>
                    <h2 id="tags-title">Popular <span>Tags</span></h2>
                    <p>Filter content by your favorite genres and themes</p>
                </div>
            </header>
            <div class="fandom-tags__cloud" data-tags-cloud>
                @foreach($tags as $index => $tag)
                    <a href="{{ route('public.fandom', $fandom['slug']) }}?tag={{ $tag->id }}"
                       class="fandom-tag {{ ($filters['tag'] ?? '') == $tag->id ? 'fandom-tag--active' : '' }}"
                       data-animate="scale-up"
                       data-delay="{{ min($index * 20, 200) }}">
                        {{ $tag->name }}
                        <span class="fandom-tag__count">{{ $tag->contents_count ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- CTA Section -->
    <section class="fandom-cta" aria-labelledby="cta-title">
        <div class="fandom-container">
            <div class="fandom-cta__card" data-animate="scale-up">
                <div class="fandom-cta__bg" aria-hidden="true" style="background: {{ $fandom['gradient'] }};"></div>
                <div class="fandom-cta__particles" aria-hidden="true" style="--particle-colors: {{ implode(',', $fandom['particles']) }};"></div>
                <div class="fandom-cta__content">
                    <h2 id="cta-title">Create Your {{ $fandom['name'] }} Story</h2>
                    <p>Share your passion with millions of fans. Write articles, upload art, post videos, and build your following.</p>
                    <a href="{{ route('user.submissions.create', ['category' => $fandom['slug']]) }}" class="fandom-btn fandom-btn--light" data-magnetic>
                        <span>Start Creating</span>
                        <x-site-icon name="plus" />
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    @vite('resources/js/modules/fandom-landing.js')
@endpush