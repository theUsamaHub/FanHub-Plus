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
    <a class="fh-skip" href="#main-content">Skip to content</a>
    <x-navbar />
    <main id="main-content" tabindex="-1" class="fh-main @yield('main-class')">@yield('content')</main>
    <x-footer />
    @include('partials.chatbot')
    @include('partials.merchandise-feedback')
    @stack('scripts')
</body>
</html>
