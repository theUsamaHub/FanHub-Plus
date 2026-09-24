<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#05050d">

        <title>{{ config('app.name', 'FanHubPlus') }} — Admin</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Saira:wght@400;500;600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.scss', 'resources/css/admin.css', 'resources/js/app.js'])
        <style>[x-cloak] { display: none !important; }</style>
        @stack('styles')
    </head>
    <body class="fh-admin">
        <div class="fh-adm-shell">
            @include('layouts.sidebar')

            <div class="fh-adm-main">
                @include('layouts.topbar')

                <main class="fh-adm-main-inner" id="main-content">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        @include('partials.command-palette')
        @stack('scripts')
    </body>
</html>
