@extends('layouts.public')
@section('title', 'Sitemap | Fan Hub Plus')
@push('styles')
    @vite('resources/css/pages/static-page.css')
@endpush
@section('content')
@php
    $groups = [
        ['id' => 'explore', 'title' => 'Explore & discover', 'icon' => 'compass', 'description' => 'Your next obsession starts here.', 'links' => [
            ['Home', route('home')], ['All discoveries', route('public.explore')],
            ['Trending', route('public.explore', ['sort' => 'popular'])], ['Featured stories', route('public.explore', ['featured' => 1])],
            ['Characters', route('public.section', 'characters')], ['Videos, art & audio', route('public.section', 'multimedia')],
            ['Upcoming releases', route('public.section', 'upcoming')], ['Merchandise', route('public.section', 'merchandise')],
            ['Events', route('events.index')], ['Events near you', route('events.nearby')],
        ]],
        ['id' => 'your-space', 'title' => 'Your space', 'icon' => 'person-circle', 'description' => 'Keep your favourite worlds close.', 'links' => [
            ['My dashboard', route('public.account', 'dashboard')], ['Bookmarks', route('public.account', 'bookmarks')],
            ['Favourite fandoms', route('user.favorites')], ['Profile & preferences', route('profile.edit')],
            ['Recent activity', route('user.activity')], ['My reviews', route('user.reviews')],
            ['My submissions', route('user.submissions')], ['Submit content', route('public.account', 'submit-content')],
        ]],
        ['id' => 'about-help', 'title' => 'About & support', 'icon' => 'chat-square-heart', 'description' => 'Get to know us. Get in touch.', 'links' => [
            ['About FanHub Plus', route('public.about')], ['Services', route('public.services')],
            ['Pricing', route('public.pricing')], ['Contact us', route('public.contact')],
            ['Send feedback', route('user.feedback')], ['Privacy policy', route('public.section', 'privacy')],
            ['Terms', route('public.section', 'terms')],
        ]],
    ];
@endphp
<div class="fh-info fh-info-page sitemap-page">
    <nav class="fh-info-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span aria-current="page">Sitemap</span></nav>
    <header class="sitemap-hero">
        <div><p class="fh-info-kicker">FANHUB PLUS / SITEMAP</p><h1>Every world.<br><em>One clear path.</em></h1><p class="sitemap-lead">Stories, fandoms, and your own corner of the community. Find exactly where you want to go.</p></div>
        <aside class="sitemap-guide"><i class="bi bi-signpost-split" aria-hidden="true"></i><span>YOUR GUIDE TO FANHUB PLUS</span><p>A little curiosity.<br>A whole universe to explore.</p><a href="{{ route('public.explore') }}">Start exploring <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></aside>
    </header>
    <nav class="sitemap-jumps" aria-label="Sitemap sections">
        <span>JUMP TO</span>@foreach($groups as $group)<a href="#{{ $group['id'] }}">{{ $group['title'] }} <i class="bi bi-arrow-down-short" aria-hidden="true"></i></a>@endforeach<a href="#fandoms">Fandoms <i class="bi bi-arrow-down-short" aria-hidden="true"></i></a>
    </nav>
    <div class="sitemap-grid">
        @foreach($groups as $group)
            <nav class="sitemap-card" id="{{ $group['id'] }}" aria-labelledby="{{ $group['id'] }}-title">
                <div class="sitemap-card__top"><span class="sitemap-icon"><i class="bi bi-{{ $group['icon'] }}" aria-hidden="true"></i></span><span class="sitemap-number">0{{ $loop->iteration }}</span></div>
                <h2 id="{{ $group['id'] }}-title">{{ $group['title'] }}</h2><p>{{ $group['description'] }}</p>
                <ul>@foreach($group['links'] as [$label, $url])<li><a href="{{ $url }}"><span>{{ $label }}</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></li>@endforeach</ul>
                @if($group['id'] === 'your-space')@guest<div class="sitemap-account"><a href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}">Create account <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>@endguest
                @endif
            </nav>
        @endforeach
    </div>
    <nav class="sitemap-fandoms" id="fandoms" aria-labelledby="sitemap-fandoms-title">
        <div><p class="fh-info-kicker">FIND YOUR PEOPLE</p><h2 id="sitemap-fandoms-title">Pick your universe.</h2><p>Follow the worlds you love, or discover something new.</p></div>
        <ul>@foreach(config('fandoms') as $slug => $fandom)<li><a href="{{ route('public.explore', ['category' => $slug]) }}"><span>{{ $fandom['name'] }}</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></li>@endforeach</ul>
    </nav>
    <div class="sitemap-help"><span><i class="bi bi-question-circle" aria-hidden="true"></i> Still looking for something?</span><a href="{{ route('public.contact') }}">We're here to help <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
</div>
@endsection
