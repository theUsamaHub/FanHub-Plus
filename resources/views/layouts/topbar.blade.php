<!-- Topbar -->
<nav class="navbar bg-white border-bottom px-3" style="min-height: 64px;">
    <div class="d-flex align-items-center">
        <!-- Mobile Hamburger -->
        <button class="btn btn-link text-dark d-lg-none p-1" onclick="toggleSidebar()">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <div class="ms-auto d-flex align-items-center">
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-link text-dark text-decoration-none d-flex align-items-center p-0" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <span class="text-white fw-semibold" style="font-size: 0.75rem;">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <span class="ms-2 d-none d-md-inline" style="font-size: 0.875rem;">{{ Auth::user()->name }}</span>
                <span class="badge bg-label-primary ms-2 d-none d-md-inline-flex text-primary" style="font-size: 0.65rem;">
                    {{ Auth::user()->hasRole('admin') ? __('Admin') : __('User') }}
                </span>
                <i class="bi bi-chevron-down ms-1" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <span class="dropdown-item-text text-muted" style="font-size: 0.75rem;">
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
