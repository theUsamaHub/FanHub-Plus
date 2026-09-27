@extends('layouts.public')
@section('title', 'Privacy Policy | Fan Hub Plus')
@push('styles')
    @vite('resources/css/pages/privacy.css')
@endpush
@section('content')
@php
    $sections = [
        'information' => ['Information you provide', 'When you create an account, you provide account details such as your name and email address. You may also add favorite fandoms, bookmarks, content submissions, and event registrations. Contact forms and feedback include the details and messages you choose to send.'],
        'use' => ['How information is used', 'Account information supports sign-in and account management. Your selected fandoms help personalize discovery. Submissions, registrations, and messages support the features you use and allow the team to review content and respond to requests.'],
        'visibility' => ['Your content and visibility', 'Content approved for publication can be visible to other visitors. Consider what personal information you include in a submission before sharing it. Information sent through a contact or feedback form is intended for the team reviewing that request.'],
        'cookies' => ['Cookies and preferences', 'The site uses session cookies for features such as signing in and protecting form submissions. Your browser may also retain display preferences. You can manage cookies and site storage in your browser; blocking them may prevent account features from working.'],
        'location' => ['Nearby events and location', 'Nearby event search asks your browser for location permission when you use that feature. Your coordinates are sent with the search to calculate distances. You can decline permission, turn it off in browser settings, or browse events without nearby search.'],
        'links' => ['External services and links', 'Links to community platforms and other websites take you outside FanHub Plus. Those services handle information under their own policies. Review their privacy settings before signing in or sharing information there.'],
        'choices' => ['Your choices and requests', 'You can update your fandom selections and bookmarks through your account. For questions about account information, corrections, or deletion requests, contact the team using the link below. Include enough detail to identify your request, but never send your password.'],
        'updates' => ['Updates to this page', 'This page may be revised as site features and information handling change. Check this page for the current explanation of how information is used.'],
    ];
@endphp
<div class="privacy-page">
    <nav class="privacy-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span aria-current="page">Privacy Policy</span></nav>
    <header class="privacy-hero">
        <div><p class="privacy-kicker">FANHUB PLUS / PRIVACY</p><h1>Your information.<br><em>Your choices.</em></h1><p class="privacy-lead">Understand what you share with FanHub Plus, how we use it, and how to manage your information.</p><a class="privacy-link" href="#information">Read our privacy policy <x-site-icon name="arrow" /></a></div>
        <aside class="privacy-summary" aria-labelledby="privacy-summary-title"><p class="privacy-kicker">AT A GLANCE</p><h2 id="privacy-summary-title">You decide what<br>to share.</h2><ul><li><x-site-icon name="user" /><span>Manage your account and fandom preferences.</span></li><li><x-site-icon name="map-pin" /><span>Choose whether to use location for nearby events.</span></li><li><x-site-icon name="mail" /><span>Contact our team about your information.</span></li></ul><a href="{{ route('public.contact') }}">Ask a privacy question <x-site-icon name="arrow" /></a></aside>
    </header>
    <div class="privacy-layout">
        <aside><nav class="privacy-toc" aria-label="Policy sections"><p class="privacy-kicker">ON THIS PAGE</p>@foreach($sections as $id => $section)<a href="#{{ $id }}"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $section[0] }}</a>@endforeach</nav></aside>
        <article class="privacy-body" aria-label="Privacy Policy"><div class="privacy-intro"><p class="privacy-kicker">THE DETAILS</p><h2>Privacy Policy</h2><p>This policy describes information used by FanHub Plus when you browse, create an account, or take part in the community.</p></div>
            @foreach($sections as $id => $section)<section id="{{ $id }}" aria-labelledby="heading-{{ $id }}"><span class="privacy-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><h2 id="heading-{{ $id }}">{{ $section[0] }}</h2><p>{{ $section[1] }}</p></div></section>@endforeach
            <footer class="privacy-contact"><x-site-icon name="mail" /><div><h2>Questions about your privacy?</h2><p>Get in touch with the FanHub Plus team.</p><a class="privacy-link" href="{{ route('public.contact') }}">Contact us <x-site-icon name="arrow" /></a></div></footer>
        </article>
    </div>
</div>
@endsection
