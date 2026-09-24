<nav class="fh-adm-topbar" aria-label="{{ __('Admin toolbar') }}">
    <button type="button" class="fh-adm-hamburger d-lg-none" onclick="toggleSidebar()" aria-label="{{ __('Open menu') }}">
        <i class="bi bi-list fs-4"></i>
    </button>

    <button type="button" class="fh-adm-search-trigger" onclick="window.dispatchEvent(new Event('toggle-command-palette'))">
        <i class="bi bi-search"></i>
        <span>{{ __('Search pages...') }}</span>
        <kbd class="fh-adm-kbd">Ctrl K</kbd>
    </button>

    <div class="ms-auto d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="fh-adm-user-chip" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                <span class="fh-adm-avatar" style="width:32px;height:32px;font-size:0.8rem;">{{ substr(Auth::user()->name, 0, 1) }}</span>
                <span class="d-none d-md-inline" style="font-size:0.875rem;">{{ Auth::user()->name }}</span>
                <i class="bi bi-chevron-down" style="font-size:0.75rem;color:var(--fh-adm-dim);"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <span class="dropdown-item-text" style="font-size: 0.75rem; color: var(--fh-adm-dim);">
                        {{ Auth::user()->email }}
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ Auth::user()->hasRole('admin') ? route('admin.dashboard') : route('user.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>{{ __('Dashboard') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person me-2"></i>{{ __('Profile') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

@push('scripts')
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('show-mobile');
        overlay.style.display = sidebar.classList.contains('show-mobile') ? 'block' : 'none';
    }
</script>
@endpush
