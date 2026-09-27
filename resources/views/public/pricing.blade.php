@extends('layouts.public')
@section('title', 'Pricing | Fan Hub Plus')
@push('styles')
    @vite('resources/css/pages/static-page.css')
@endpush
@section('content')
<div class="fh-info fh-info-page">
    <nav class="fh-info-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span aria-current="page">Pricing</span></nav>
    <header class="fh-info-hero">
        <div>
            <p class="fh-info-kicker">FANHUB PLUS / PRICING</p>
            <h1>Free for<br><em>every fan.</em></h1>
            <p class="fh-info-lead">Reading, discovery, events and collections cost nothing. One plan covers everything on FanHub Plus — because fandom should never sit behind a paywall.</p>
            <div class="fh-info-actions">
                <a class="fh-button" href="{{ route('register') }}">Create free account</a>
                <a class="fh-info-link" href="{{ route('public.contact') }}">Ask about teams <x-site-icon name="arrow" /></a>
            </div>
        </div>
        <aside class="fh-info-panel" aria-label="Pricing summary">
            <p class="fh-info-kicker">THE SHORT VERSION</p>
            <h2>$0 today.<br>$0 planned.</h2>
            <ul class="fh-info-facts">
                <li><x-site-icon name="shield" /><span><strong>No card required</strong>Create an account with just an email and password.</span></li>
                <li><x-site-icon name="crown" /><span><strong>No premium tier</strong>Every fan gets the same full experience.</span></li>
                <li><x-site-icon name="people" /><span><strong>Community funded direction</strong>Features ship based on fan feedback, not upsells.</span></li>
            </ul>
        </aside>
    </header>

    <div class="fh-info-sections">
        <h2>Plans</h2>
        <p class="fh-info-sub">One plan. Everything included. Sign in on any device and your favorites, bookmarks and submissions follow you.</p>
        <div class="fh-info-plans">
            <article class="fh-info-plan">
                <p class="fh-info-kicker">FAN PASS</p>
                <h3>Free forever</h3>
                <p class="fh-info-price">$0<span> / month</span></p>
                <p>No trial, no ads on the reading experience, no feature gates.</p>
                <ul class="fh-info-list">
                    <li>Personalised feed across {{ count(config('fandoms')) }} fandoms</li>
                    <li>Stories, reviews and character hubs</li>
                    <li>Events calendar and nearby search</li>
                    <li>Merchandise collection and bookmarks</li>
                    <li>Content submissions and feedback</li>
                    <li>Favorites, activity history and profile</li>
                </ul>
                <a class="fh-button" href="{{ route('register') }}">Get all that</a>
            </article>
            <article class="fh-info-plan">
                <p class="fh-info-kicker">FOR GROUPS</p>
                <h3>Community &amp; partners</h3>
                <p class="fh-info-price">Talk<span> to us</span></p>
                <p>Running a club, convention or store? Let's find the right fit together.</p>
                <ul class="fh-info-list">
                    <li>Feature your events to targeted fandoms</li>
                    <li>List merchandise drops with release dates</li>
                    <li>Publish reviewed content to fans who follow it</li>
                    <li>Direct line to the team through the contact inbox</li>
                </ul>
                <a class="fh-button" href="{{ route('public.contact') }}">Contact the team</a>
            </article>
        </div>
    </div>

    <div class="fh-info-sections">
        <h2>Questions, answered</h2>
        <p class="fh-info-sub">The details fans ask about most.</p>
        <div class="fh-info-faq">
            <details open>
                <summary>Is FanHub Plus really free?</summary>
                <p>Yes. Browsing, reading, events, merchandise tracking and community features are free for every account, with no premium tier planned.</p>
            </details>
            <details>
                <summary>Do I need an account to browse?</summary>
                <p>No. You can explore fandoms, stories, events and the collection as a guest. An account adds favorites, bookmarks, submissions and personalised feeds.</p>
            </details>
            <details>
                <summary>How do content submissions work?</summary>
                <p>Members submit stories, reviews and merchandise finds from their dashboard. The team reviews each submission and notifies you when it is published or needs changes.</p>
            </details>
            <details>
                <summary>Can I use it on my phone?</summary>
                <p>FanHub Plus is fully responsive — the same account, feed and bookmarks work across desktop, tablet and mobile browsers.</p>
            </details>
        </div>
    </div>

    <footer class="fh-info-cta">
        <x-site-icon name="mail" />
        <div>
            <h2>Still deciding?</h2>
            <p>Create an account in a minute, or ask us anything first — we answer every message.</p>
            <a class="fh-info-link" href="{{ route('register') }}">Join free <x-site-icon name="arrow" /></a>
        </div>
    </footer>
</div>
@endsection
