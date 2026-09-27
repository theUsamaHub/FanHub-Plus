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

        {{-- SweetAlert2 for styled confirm/alert dialogs in the admin panel --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        {{-- Global SweetAlert2 helpers: any <form data-confirm="..."> or <button data-confirm="...">
             shows a styled dialog instead of the native browser confirm(). --}}
        <script>
            (function () {
                if (typeof window.Swal === 'undefined') return;

                const isDark = function () { return document.documentElement.dataset.theme !== 'light'; };

                function dialogConfig(extra) {
                    const dark = isDark();
                    const base = {
                        background: dark ? '#15151f' : '#ffffff',
                        color: dark ? '#e8e8ee' : '#1f1f2e',
                        confirmButtonColor: dark ? '#7a5cff' : '#0d6efd',
                        cancelButtonColor: dark ? '#3a3a4a' : '#6c757d',
                        customClass: {
                            popup: 'fh-swal-popup',
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-secondary',
                        },
                        reverseButtons: true,
                        focusCancel: true,
                    };
                    return Object.assign(base, extra || {});
                }

                async function ask(title) {
                    return Swal.fire(dialogConfig({
                        title: title,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, confirm',
                        cancelButtonText: 'Cancel',
                    }));
                }

                async function info(title, icon) {
                    return Swal.fire(dialogConfig({
                        title: title,
                        icon: icon || 'info',
                        showCancelButton: false,
                        confirmButtonText: 'OK',
                    }));
                }

                // Programmatic helpers for inline scripts.
                window.fhpConfirm = async function (message) {
                    const r = await ask(message);
                    return !!(r && r.isConfirmed);
                };
                window.fhpAlert = async function (message) {
                    return info(message, 'info');
                };

                // Delegation: any form with [data-confirm] shows SweetAlert first.
                document.addEventListener('submit', function (e) {
                    const form = e.target;
                    if (!(form instanceof HTMLFormElement)) return;
                    const message = form.getAttribute('data-confirm');
                    if (!message) return;
                    e.preventDefault();
                    ask(message).then(function (r) {
                        if (r && r.isConfirmed) form.submit();
                    });
                }, true);

                // Delegation: any <button data-confirm> or <a data-confirm> standalone.
                document.addEventListener('click', function (e) {
                    const el = e.target.closest('button[data-confirm], a[data-confirm]');
                    if (!el) return;
                    const message = el.getAttribute('data-confirm');
                    if (!message) return;
                    e.preventDefault();
                    ask(message).then(function (r) {
                        if (!r || !r.isConfirmed) return;
                        const tag = el.tagName;
                        if (tag === 'A') {
                            window.location.href = el.getAttribute('href');
                        } else if (el.form instanceof HTMLFormElement) {
                            el.form.submit();
                        }
                    });
                }, true);

                // Style the popup to match the admin theme.
                const style = document.createElement('style');
                style.textContent = '.swal2-popup.fh-swal-popup{border-radius:14px;padding:1.75rem;box-shadow:0 24px 60px rgba(0,0,0,.45);font-family:inherit}.swal2-popup.fh-swal-popup .swal2-title{font-weight:600;font-size:1.15rem}.swal2-popup.fh-swal-popup .swal2-actions{gap:.5rem;margin-top:1.25rem}.swal2-popup.fh-swal-popup .swal2-styled.swal2-confirm{padding:.5rem 1.25rem;font-weight:500}.swal2-popup.fh-swal-popup .swal2-styled.swal2-cancel{padding:.5rem 1.25rem;font-weight:500;margin-right:.5rem}';
                document.head.appendChild(style);
            })();
        </script>

        @stack('modals')
        @stack('scripts')
    </body>
</html>
