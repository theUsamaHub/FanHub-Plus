@extends('layouts.public')
@section('title', 'Services | Fan Hub Plus')
@push('styles')
    @vite('resources/css/pages/static-page.css')
@endpush
@section('content')
<div class="fh-info fh-info-page">
    <nav class="fh-info-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span aria-current="page">Services</span></nav>
    <header class="fh-info-hero">
        <div>
            <p class="fh-info-kicker">FANHUB PLUS / SERVICES</p>
            <h1>Everything for<br><em>your fandom.</em></h1>
            <p class="fh-info-lead">Discovery, stories, characters, events and collections — six things FanHub Plus does for fans every day, without a paywall.</p>
            <div class="fh-info-actions">
                <a class="fh-button" href="{{ route('public.explore') }}">Explore fandoms</a>
                <a class="fh-info-link" href="{{ route('public.contact') }}">Ask a question <x-site-icon name="arrow" /></a>
            </div>
        </div>
        <aside class="fh-info-panel" aria-label="Services summary">
            <p class="fh-info-kicker">WHAT YOU GET</p>
            <h2>One account.<br>Six ways in.</h2>
            <ul class="fh-info-facts">
                <li><x-site-icon name="compass" /><span><strong>Fandom discovery</strong>Search and browse {{ count(config('fandoms')) }} curated fandom hubs.</span></li>
                <li><x-site-icon name="bell" /><span><strong>Release tracking</strong>Upcoming drops, schedules and reminders per fandom.</span></li>
                <li><x-site-icon name="chat" /><span><strong>Community voice</strong>Reviews, ratings, feedback and content submissions.</span></li>
            </ul>
        </aside>
    </header>

    <div class="fh-info-sections">
        <h2>Our services</h2>
        <p class="fh-info-sub">Each service works on its own — together they make FanHub Plus the single home for everything you follow.</p>
        <div class="fh-info-grid">
            <article class="fh-info-card">
                <x-site-icon name="compass" />
                <h3>Fandom discovery</h3>
                <p>Curated hubs for anime, comics, gaming, K-pop, cosplay and more — with trending, featured and newest content sorted for you.</p>
                <a class="fh-info-link" href="{{ route('public.explore') }}">Open discovery <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="book" />
                <h3>Stories and reviews</h3>
                <p>Community-submitted articles, lore explainers, guides and reviews — moderated by the team before they go live.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'multimedia') }}">Read stories <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="mask" />
                <h3>Character hub</h3>
                <p>Profiles for the characters you follow, linked to related stories, titles and merchandise across fandoms.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'characters') }}">Browse characters <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="clock" />
                <h3>Upcoming releases</h3>
                <p>A calendar of what drops next — merch, titles and events — filtered by the fandoms you actually care about.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'upcoming') }}">See what's next <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="map-pin" />
                <h3>Events and nearby search</h3>
                <p>Conventions, expos and fan meetups with full details, calendars and optional location-based search near you.</p>
                <a class="fh-info-link" href="{{ route('events.index') }}">Find events <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="bag" />
                <h3>Merchandise collection</h3>
                <p>Limited editions, pre-orders and collectibles with release dates, category filters and bookmarkable pieces.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'merchandise') }}">Browse merchandise <x-site-icon name="arrow" /></a>
            </article>
        </div>
    </div>

    <div class="fh-info-sections">
        <div class="fh-info-cols">
            <div class="fh-info-block">
                <h3>For members</h3>
                <ul class="fh-info-list">
                    <li>Free account with personalised fandom feed.</li>
                    <li>Bookmarks, favorites and submission history.</li>
                    <li>Ratings, reviews and feedback on anything published.</li>
                    <li>Notification of approvals when your submission goes live.</li>
                </ul>
            </div>
            <div class="fh-info-block">
                <h3>For creators and partners</h3>
                <ul class="fh-info-list">
                    <li>Publish reviewed content to a targeted fandom audience.</li>
                    <li>List merchandise drops with dates and categories.</li>
                    <li>Get event in front of fans searching for exactly that.</li>
                    <li>Start a conversation through the contact inbox.</li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="fh-info-cta">
        <x-site-icon name="mail" />
        <div>
            <h2>Not sure where to start?</h2>
            <p>Tell us what you follow and we will point you to the right hub.</p>
            <a class="fh-info-link" href="{{ route('public.contact') }}">Contact us <x-site-icon name="arrow" /></a>
        </div>
    </footer>
</div>
@endsection
