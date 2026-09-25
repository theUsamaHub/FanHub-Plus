<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#06060e">
    <title>@yield('title', 'Fan Hub Plus — Every Universe. One Home.')</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('fanhub-theme') === 'light' ? 'light' : 'dark'; } catch (e) {}</script>
    @vite('resources/js/public.js')
    @stack('styles')
</head>
<body class="fh-site">
    <!-- Video Splash Screen -->
    <div id="splash-screen" style="position:fixed;inset:0;z-index:9999;background:#000;display:flex;align-items:center;justify-content:center;transition:opacity 0.8s ease;">
        <video id="splash-video" autoplay muted playsinline preload="auto" style="width:100%;height:100%;object-fit:cover;">
            <source src="{{ asset('videos/girl-behind-curtains-3.1920x1080.mp4') }}" type="video/mp4">
        </video>
        <button id="splash-skip" style="position:absolute;top:20px;right:20px;z-index:10000;background:rgba(0,0,0,0.5);color:white;border:1px solid rgba(255,255,255,0.3);padding:8px 16px;border-radius:4px;cursor:pointer;font-family:'Saira',sans-serif;font-size:14px;transition:background 0.2s;">Skip</button>
    </div>
    <script>
    (function(){
        var splash=document.getElementById('splash-screen'),
            video=document.getElementById('splash-video'),
            skip=document.getElementById('splash-skip');
        function hide(){splash.style.opacity='0';setTimeout(function(){splash.style.display='none'},800)}
        skip.addEventListener('click',hide);
        video.addEventListener('ended',hide);
        setTimeout(hide,15000);
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
