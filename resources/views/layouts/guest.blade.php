<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <title>{{ config('app.name', 'FanHubPlus') }}</title>


        @vite(['resources/css/app.scss', 'resources/js/app.js'])
        @include('partials.font-links')
    </head>
    <body class="auth-wrapper">
        <div class="auth-card">
            <div class="text-center mb-4">
                <a href="/" class="text-decoration-none">
                    <x-application-logo class="w-16 h-16 mx-auto" />
                </a>
                <h4 class="mt-2 fw-semibold text-dark">{{ config('app.name', 'FanHubPlus') }}</h4>
            </div>

            {{ $slot }}
        </div>
    </body>
</html>
