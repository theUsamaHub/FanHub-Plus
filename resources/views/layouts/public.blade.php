<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#06060e">
    <title>@yield('title', 'Fan Hub Plus — Every Universe. One Home.')</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('fanhub-theme') === 'light' ? 'light' : 'dark'; } catch (e) {}</script>
    @auth
    <meta name="member-preferences-url" content="{{ route('user.preferences') }}">
    <script>
        (() => {
            const theme = @json(auth()->user()->profile?->theme_preference);
            if (theme) document.documentElement.dataset.theme = theme === 'system' ? (matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark') : theme;
            document.documentElement.dataset.fontSize = @json(auth()->user()->profile?->font_size_preference ?? 'medium');
        })();
    </script>
    @endauth
    @vite('resources/js/public.js')
    @stack('styles')
</head>
<body class="fh-site">
    <!-- Video Splash Screen -->
    <div id="splash-screen" style="position:fixed;inset:0;z-index:9999;background:#000;display:flex;align-items:center;justify-content:center;transition:opacity 0.8s ease;">
        <!-- Desktop: Video -->
        <video id="splash-video" class="splash-desktop-only" autoplay muted playsinline preload="auto" style="width:100%;height:100%;object-fit:cover;">
            <source src="{{ asset('videos/splash screen video.mp4') }}" type="video/mp4">
        </video>
        <!-- Mobile: Animation -->
        <div class="splash-mobile-only" style="display:none;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
            <style>
                @keyframes splashPulse { 0%,100%{opacity:.3;transform:scale(.95)} 50%{opacity:1;transform:scale(1)} }
                @keyframes splashFadeUp { 0%{opacity:0;transform:translateY(12px)} 100%{opacity:1;transform:translateY(0)} }
                @keyframes splashSpin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }
                .splash-mobile-only { animation: splashFadeUp .5s ease forwards; }
                .splash-mobile-only h1 { font-family:'Rajdhani',sans-serif; font-size:32px; font-weight:700; color:#fff; margin:0 0 8px; animation: splashPulse 1.4s ease infinite; letter-spacing:-0.5px; }
                .splash-mobile-only h1 span { color:#FF922E; }
                .splash-mobile-only p { font-family:'Saira',sans-serif; font-size:12px; color:rgba(255,255,255,.5); letter-spacing:4px; text-transform:uppercase; margin:0 0 24px; }
                .splash-ring { width:36px; height:36px; border:2px solid rgba(255,255,255,.1); border-top-color:#FF922E; border-radius:50%; animation: splashSpin .7s linear infinite; }
            </style>
            <h1>FAN<span>HUB+</span></h1>
            <p>Every universe. One home.</p>
            <div class="splash-ring" aria-hidden="true"></div>
        </div>
        <button id="splash-skip" style="position:absolute;top:20px;right:20px;z-index:10000;background:rgba(0,0,0,0.5);color:white;border:1px solid rgba(255,255,255,.3);padding:8px 16px;border-radius:4px;cursor:pointer;font-family:'Saira',sans-serif;font-size:14px;transition:background .2s;">Skip</button>
    </div>
    <style>
        @media(max-width:700px){
            .splash-desktop-only{display:none!important}
            .splash-mobile-only{display:flex!important}
        }
    </style>
    <script>
    (function(){
        var splash=document.getElementById('splash-screen'),
            video=document.getElementById('splash-video'),
            skip=document.getElementById('splash-skip');
        function hide(){splash.style.opacity='0';setTimeout(function(){splash.style.display='none'},800)}
        skip.addEventListener('click',hide);
        video.addEventListener('ended',hide);
        setTimeout(hide,2000);
    })();
    </script>
    <!-- End Video Splash Screen -->

    <a class="fh-skip" href="#main-content">Skip to content</a>
    <x-navbar />
    <main id="main-content" tabindex="-1" class="fh-main @yield('main-class')">@yield('content')</main>
    <x-footer />
    @include('partials.chatbot')
    @include('partials.merchandise-feedback')
    @stack('scripts')
</body>
</html>
