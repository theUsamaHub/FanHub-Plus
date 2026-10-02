<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
    @include('partials.font-links')
    @stack('styles')
</head>
<body class="fh-site">
    @include('partials.splash')

    <a class="fh-skip" href="#main-content">Skip to content</a>
    <x-navbar />
    <main id="main-content" tabindex="-1" class="fh-main @yield('main-class')">@yield('content')</main>
    <x-footer />
    @if(session('onboarding-success'))<div class="onboarding-toast" role="status">{{ session('onboarding-success') }}</div>@endif
    @include('partials.chatbot')
    @include('partials.merchandise-feedback')
    @stack('scripts')
</body>
</html>
