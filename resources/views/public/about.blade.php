@extends('layouts.public')
@section('title', 'About | Fan Hub Plus')
@push('styles')
    @vite('resources/css/pages/static-page.css')
@endpush
@section('content')
<div class="fh-info fh-info-page">
    <nav class="fh-info-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span aria-current="page">About</span></nav>
    <header class="fh-info-hero">
        <div>
            <p class="fh-info-kicker">FANHUB PLUS / ABOUT</p>
            <h1>Every universe.<br><em>One home.</em></h1>
            <p class="fh-info-lead">FanHub Plus is where fans follow the stories, characters, events and collections they love — across every fandom, in one place.</p>
            <div class="fh-info-actions">
                <a class="fh-button" href="{{ route('public.explore') }}">Explore fandoms</a>
                <a class="fh-info-link" href="{{ route('public.contact') }}">Talk to the team <x-site-icon name="arrow" /></a>
            </div>
        </div>
        <aside class="fh-info-panel" aria-label="About FanHub Plus at a glance">
            <p class="fh-info-kicker">AT A GLANCE</p>
            <h2>Built by fans,<br>for fans.</h2>
            <ul class="fh-info-facts">
                <li><x-site-icon name="compass" /><span><strong>{{ count(config('fandoms')) }} fandoms and counting</strong>Anime, comics, gaming, K-pop, cosplay and more.</span></li>
                <li><x-site-icon name="book" /><span><strong>Stories the community writes</strong>Articles, reviews and deep dives submitted by fans.</span></li>
                <li><x-site-icon name="clock" /><span><strong>Events and releases on time</strong>Conventions, meetups and upcoming drops in one feed.</span></li>
                <li><x-site-icon name="bag" /><span><strong>A curated collection</strong>Limited editions, pre-orders and collectibles worth tracking.</span></li>
            </ul>
        </aside>
    </header>

    <div class="fh-info-sections">
        <h2>Why FanHub Plus exists</h2>
        <p class="fh-info-sub">Fans should not need five apps, three newsletters and a dozen tabs to follow one universe. We brought discovery, reading, events and collections together so your fandom lives in a single home.</p>
        <div class="fh-info-grid">
            <article class="fh-info-card">
                <x-site-icon name="search" />
                <h3>Discover your fandom</h3>
                <p>Browse featured fandoms, trending stories and new releases filtered to what you actually follow. Save favorites and pick up where you left off.</p>
                <a class="fh-info-link" href="{{ route('public.explore') }}">Start exploring <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="book" />
                <h3>Read what fans write</h3>
                <p>Articles, lore deep dives, reviews and guides from the community — reviewed before publication so quality stays high.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'multimedia') }}">Browse stories <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="mask" />
                <h3>Follow characters</h3>
                <p>Character hubs collect appearances, related stories and fan favourites so you can track a whole cast across titles.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'characters') }}">Meet the characters <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="map-pin" />
                <h3>Never miss an event</h3>
                <p>Conventions, expos and watch parties with calendars, details and nearby search when you want to attend in person.</p>
                <a class="fh-info-link" href="{{ route('events.index') }}">See events <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="bag" />
                <h3>Track the collection</h3>
                <p>Upcoming merchandise, limited editions and pre-orders in one place — bookmark pieces and get release reminders.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'merchandise') }}">Open the collection <x-site-icon name="arrow" /></a>
            </article>
            <article class="fh-info-card">
                <x-site-icon name="people" />
                <h3>Shape the platform</h3>
                <p>Members submit content, leave reviews, send feedback and vote on what gets published next. The roadmap is a community effort.</p>
                <a class="fh-info-link" href="{{ route('public.section', 'feedback') }}">Share feedback <x-site-icon name="arrow" /></a>
            </article>
        </div>
    </div>

    <div class="fh-info-sections">
        <div class="fh-info-cols">
            <div class="fh-info-block">
                <h3>How it works</h3>
                <ul class="fh-info-list">
                    <li>Create a free account and pick your favourite fandoms.</li>
                    <li>Your feed fills with stories, releases and events from those fandoms.</li>
                    <li>Submit your own content or merchandise finds for review.</li>
                    <li>Bookmark characters and collections to follow them closely.</li>
                </ul>
            </div>
            <div class="fh-info-block">
                <h3>What we stand for</h3>
                <ul class="fh-info-list">
                    <li>Fans first — every feature starts from a fan problem.</li>
                    <li>Reviewed content — human eyes before anything is published.</li>
                    <li>Respect for creators — spoilers, credit and conduct are enforced.</li>
                    <li>No paywalls on the basics — reading and discovery stay free.</li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="fh-info-cta">
        <x-site-icon name="mail" />
        <div>
            <h2>Want to build this with us?</h2>
            <p>Join free, follow your fandoms, and tell us what to add next.</p>
            <a class="fh-info-link" href="{{ route('register') }}">Create a free account <x-site-icon name="arrow" /></a>
        </div>
    </footer>
</div>
@endsection
