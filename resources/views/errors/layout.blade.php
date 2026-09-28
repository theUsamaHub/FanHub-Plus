<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') · @yield('label') | FanHub Plus</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('fanhub-theme') === 'light' ? 'light' : 'dark'; } catch (e) {}</script>
    @include('partials.font-links')
    <style>@include('errors.styles')</style>
</head>
<body class="error-page">
    <header class="error-header"><x-site-brand /><a href="{{ url('/') }}" class="error-home">Back to home <span aria-hidden="true">↗</span></a></header>
    <main class="error-main" id="main-content">
        <div class="error-art" aria-hidden="true"><span class="error-orbit error-orbit--outer"></span><span class="error-orbit"></span><span class="error-star">✦</span><strong>@yield('code')</strong><span class="error-coordinate">FANHUB PLUS / SIGNAL LOST</span></div>
        <section class="error-content" aria-labelledby="error-title">
            <p class="error-kicker"><span></span>@yield('label')</p>
            <h1 id="error-title">@yield('title')</h1>
            <p class="error-description">@yield('message')</p>
            <div class="error-actions"><a class="error-button" href="{{ url('/') }}">Back to home <span aria-hidden="true">→</span></a><a class="error-button error-button--quiet" href="{{ url('/explore') }}">Explore fandoms <span aria-hidden="true">↗</span></a></div>
            <p class="error-help">Need a hand? <a href="{{ url('/contact') }}">Contact the team</a></p>
        </section>
    </main>
    <footer class="error-footer"><span>EVERY UNIVERSE. ONE HOME.</span><nav aria-label="Helpful pages"><a href="{{ url('/sitemap') }}">Sitemap</a><a href="{{ url('/discover/privacy') }}">Privacy</a></nav><span>© {{ date('Y') }} FanHub Plus</span></footer>
</body>
</html>
