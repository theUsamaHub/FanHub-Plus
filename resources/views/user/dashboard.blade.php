@extends('user.layout', ['pageTitle' => 'My Dashboard'])
@inject('library', 'App\Services\MemberLibrary')
@section('member-content')
<section class="member-welcome">
    <img class="member-welcome__art" src="{{ asset('images/fandoms/anime.png') }}" alt="" fetchpriority="high">
    <div class="member-welcome__copy"><p>Welcome back,</p><h1>{{ $user->profile?->display_name ?: $user->name }}</h1><p class="member-welcome__intro">Continue your fandom journey. Discover new worlds, keep track of what you love, and never miss what’s next.</p><div class="member-actions"><a class="member-button" href="{{ route('public.explore') }}">Explore Fandoms <span aria-hidden="true">↗</span></a><a class="member-button member-button--quiet" href="{{ route('user.bookmarks') }}"><i class="bi bi-bookmark" aria-hidden="true"></i> View Bookmarks</a></div></div>
    <p class="member-welcome__caption">Same fandoms.<br>New discoveries.</p>
</section>
<div class="member-stats">
    @foreach([
        ['bookmarks', 'Saved Bookmarks', 'Stories, characters and more', 'bookmark-fill', 'gold', route('user.bookmarks')],
        ['favorites', 'Favorite Fandoms', 'The worlds you love most', 'heart-fill', 'rose', route('user.favorites')],
        ['watched', 'Watched This Week', 'Your video and audio history', 'play-fill', 'green', route('user.activity', ['type' => 'watched'])],
        ['releases', 'Upcoming Releases', 'New content to look forward to', 'calendar3', 'lavender', route('public.section', 'upcoming')]
    ] as [$key, $label, $hint, $icon, $color, $url])
    <a class="member-stat" href="{{ $url }}"><span class="member-stat__icon member-tone--{{ $color }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><div><strong>{{ number_format($stats[$key]) }}</strong><h2>{{ $label }}</h2><p>{{ $hint }}</p></div><span aria-hidden="true">›</span></a>
    @endforeach
</div>
<div class="member-dashboard-grid">
    <section class="member-panel member-panel--continue"><header><h2><i class="bi bi-fire" aria-hidden="true"></i> Continue Exploring</h2><a href="{{ route('user.activity') }}">View all →</a></header><div class="member-card-grid">
        @forelse($continue as $item)<x-member-card :card="$library->card($item)" />@empty<div class="member-empty"><h3>Your next chapter starts here.</h3><p>Open a story and it will appear here, ready for your next visit.</p><a href="{{ route('public.explore') }}">Find your first story →</a></div>@endforelse
    </div></section>
    <section class="member-panel member-panel--favorites"><header><h2><i class="bi bi-heart-fill" aria-hidden="true"></i> Your Favorite Fandoms</h2><a href="{{ route('user.favorites') }}">Edit →</a></header><div class="member-favorite-grid">
        @forelse($favorites->take(6) as $category)<a class="member-favorite" href="{{ route('public.explore', ['category' => $category->slug]) }}"><img src="{{ $category->icon_url ?: asset(config('homepage.artwork.'.$category->slug, 'images/fandoms/anime.png')) }}" alt="" data-image-fallback="{{ asset('images/fandoms/anime.png') }}"><div><strong>{{ $category->name }}</strong><small>{{ $category->contents_count }} stories</small></div><span aria-hidden="true">›</span></a>@empty<div class="member-empty"><h3>Make this space yours.</h3><p>Choose your fandoms for recommendations you’ll love.</p><a href="{{ route('user.favorites') }}">Choose fandoms →</a></div>@endforelse
    </div></section>
    <section class="member-panel member-panel--activity"><header><h2><i class="bi bi-clock-fill" aria-hidden="true"></i> Recent Activity</h2><a href="{{ route('user.activity') }}">View all →</a></header>@include('user.partials.activity', ['items' => $activity])</section>
    <section class="member-panel member-panel--releases"><header><h2><i class="bi bi-calendar3" aria-hidden="true"></i> Upcoming Releases</h2><a href="{{ route('public.section', 'upcoming') }}">View all →</a></header><div class="member-release-grid">
        @forelse($releases as $release)<a class="member-release" href="{{ route('public.content', $release->slug) }}"><img src="{{ $release->artwork_url }}" alt="" data-image-fallback="{{ asset('images/fandoms/gaming.png') }}"><time><small>{{ $release->release_date?->format('M') ?? 'DATE' }}</small><strong>{{ $release->release_date?->format('d') ?? 'TBA' }}</strong></time><div><h3>{{ $release->title }}</h3><small>{{ $release->category?->name }} · {{ $release->kind_label }}</small></div></a>@empty<p class="member-empty">New releases will appear here when announced.</p>@endforelse
    </div></section>
    <section class="member-panel member-panel--recommended"><header><h2><i class="bi bi-stars" aria-hidden="true"></i> Recommended For You</h2><a href="{{ route('public.explore', ['sort' => 'popular']) }}">View all →</a></header><div class="member-card-grid">@forelse($recommendations as $item)<x-member-card :card="$library->card($item)" compact />@empty<p class="member-empty">Explore more fandoms to discover new recommendations.</p>@endforelse</div></section>
</div>
<section class="member-panel member-saved"><header><h2><i class="bi bi-bookmark" aria-hidden="true"></i> On Your Bookshelf</h2><a href="{{ route('user.bookmarks') }}">View bookmarks →</a></header><div class="member-card-grid">@forelse($bookmarks as $bookmark)@if($card = $library->card($bookmark->bookmarkable))<x-member-card :card="$card" compact />@else<p class="member-empty">A saved item is no longer available.</p>@endif @empty<p class="member-empty">Save something that catches your eye. Your collection will be waiting here.</p>@endforelse</div></section>
@endsection
