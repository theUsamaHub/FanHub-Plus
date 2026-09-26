<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#05050d">
        <script>try { const theme = localStorage.getItem('fanhub-theme'); const sidebar = localStorage.getItem('fanhub-sidebar'); if (theme === 'light') { document.documentElement.dataset.theme = 'light'; document.body.dataset.theme = 'light'; } else { document.documentElement.dataset.theme = 'dark'; document.body.dataset.theme = 'dark'; } if (sidebar === 'collapsed') { document.documentElement.dataset.sidebar = 'collapsed'; document.body.classList.add('is-sidebar-collapsed'); } else { document.documentElement.dataset.sidebar = 'open'; document.body.classList.remove('is-sidebar-collapsed'); } } catch (e) {}</script>

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
        
        {{-- Onboarding Modal --}}
        @if (auth()->check() && !auth()->user()->hasRole('admin') && auth()->user()->profile && !auth()->user()->profile->onboarding_completed_at)
            @include('auth.onboarding')
        @endif
        
        <script>
            (function () {
                const root = document.documentElement;
                const body = document.body;

                function applyTheme(theme) {
                    root.dataset.theme = theme;
                    body.dataset.theme = theme;
                    try { localStorage.setItem('fanhub-theme', theme); } catch (e) {}
                }

                function applySidebar(state) {
                    body.classList.toggle('is-sidebar-collapsed', state === 'collapsed');
                    root.dataset.sidebar = state;
                    try { localStorage.setItem('fanhub-sidebar', state); } catch (e) {}
                }

                applyTheme(root.dataset.theme === 'light' ? 'light' : 'dark');
                applySidebar(root.dataset.sidebar === 'collapsed' ? 'collapsed' : 'open');

                window.toggleTheme = function () {
                    applyTheme(root.dataset.theme === 'light' ? 'dark' : 'light');
                };

                window.toggleSidebarCollapse = function () {
                    applySidebar(body.classList.contains('is-sidebar-collapsed') ? 'open' : 'collapsed');
                };

                window.toggleSidebar = function () {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    sidebar.classList.toggle('show-mobile');
                    overlay.style.display = sidebar.classList.contains('show-mobile') ? 'block' : 'none';
                };
            })();
        </script>
        @stack('scripts')
    </body>
</html>
