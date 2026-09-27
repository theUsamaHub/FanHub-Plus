<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Make it your universe | FanHub Plus</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('fanhub-theme') === 'light' ? 'light' : 'dark'; } catch (e) {}</script>
    @vite('resources/js/public.js')
</head>
<body class="fh-site onboarding-body">
    <div class="onboarding-backdrop" aria-hidden="true" inert><span>EVERY UNIVERSE.<br>ONE HOME.</span></div>
    @include('partials.onboarding-modal')
</body>
</html>
