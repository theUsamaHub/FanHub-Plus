<aside class="fh-adm-sidebar d-none d-lg-flex" id="sidebar">
    <a class="fh-adm-brand" href="{{ route('admin.dashboard') }}">
        <svg class="fh-adm-brand-mark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 8l3.5 3L12 4l5.5 7L21 8l-1.5 10h-15L3 8z"/>
            <path d="M7 21h10"/>
        </svg>
        <div>
            <div class="fh-adm-brand-title">{{ config('app.name', 'FanHubPlus') }}</div>
            <span class="fh-adm-brand-sub">{{ __('Guild Command') }}</span>
        </div>
    </a>

    <nav class="fh-adm-nav" aria-label="{{ __('Admin navigation') }}">
        <div class="fh-adm-nav-group">{{ __('Overview') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}" href="{{ route('profile.edit') }}">
            <i class="bi bi-person"></i> {{ __('Profile') }}
        </a>

        <div class="fh-adm-nav-group">{{ __('Content') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.contents.*') ? 'is-active' : '' }}" href="{{ route('admin.contents.index') }}"><i class="bi bi-file-earmark-text"></i> {{ __('Content') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.submissions.*') ? 'is-active' : '' }}" href="{{ route('admin.submissions.index') }}"><i class="bi bi-inbox"></i> {{ __('Submissions') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.characters.*') ? 'is-active' : '' }}" href="{{ route('admin.characters.index') }}"><i class="bi bi-person-badge"></i> {{ __('Characters') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.merchandise.*') ? 'is-active' : '' }}" href="{{ route('admin.merchandise.index') }}"><i class="bi bi-box-seam"></i> {{ __('Merchandise') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.events.*') ? 'is-active' : '' }}" href="{{ route('admin.events.index') }}"><i class="bi bi-calendar-event"></i> {{ __('Events') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}" href="{{ route('admin.reviews.index') }}"><i class="bi bi-chat-left-text"></i> {{ __('Reviews') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.ratings.*') ? 'is-active' : '' }}" href="{{ route('admin.ratings.index') }}"><i class="bi bi-star"></i> {{ __('Ratings') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.feedback.*') ? 'is-active' : '' }}" href="{{ route('admin.feedback.index') }}"><i class="bi bi-megaphone"></i> {{ __('Feedback') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.analytics.*') ? 'is-active' : '' }}" href="{{ route('admin.analytics.index') }}"><i class="bi bi-graph-up"></i> {{ __('Analytics') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i> {{ __('Categories') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.categories.trashed') ? 'is-active' : '' }}" href="{{ route('admin.categories.trashed') }}"><i class="bi bi-trash"></i> {{ __('Recycle Bin') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.tags.*') ? 'is-active' : '' }}" href="{{ route('admin.tags.index') }}"><i class="bi bi-bookmark"></i> {{ __('Tags') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}" href="{{ route('admin.media.index') }}"><i class="bi bi-folder"></i> {{ __('Media') }}</a>

        <div class="fh-adm-nav-group">{{ __('Users') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> {{ __('Users') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.roles.*') ? 'is-active' : '' }}" href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-check"></i> {{ __('Roles') }}</a>

        <div class="fh-adm-nav-group">{{ __('System') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.activity-logs.index') }}"><i class="bi bi-clock-history"></i> {{ __('Activity') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.sessions.*') ? 'is-active' : '' }}" href="{{ route('admin.sessions.index') }}"><i class="bi bi-person-badge"></i> {{ __('Sessions') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.maintenance.*') ? 'is-active' : '' }}" href="{{ route('admin.maintenance.index') }}"><i class="bi bi-shield-exclamation"></i> {{ __('Maintenance') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.logs.*') ? 'is-active' : '' }}" href="{{ route('admin.logs.index') }}"><i class="bi bi-journal-text"></i> {{ __('Logs') }}</a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.backup.*') ? 'is-active' : '' }}" href="{{ route('admin.backup.index') }}"><i class="bi bi-database"></i> {{ __('Backup') }}</a>
    </nav>

    <div class="fh-adm-sidebar-foot">
        <div class="fh-adm-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
        <div class="overflow-hidden">
            <div class="fh-adm-user-name">{{ Auth::user()->name }}</div>
            <div class="fh-adm-user-role">{{ __('Admin') }}</div>
        </div>
    </div>
</aside>

<div class="sidebar-overlay d-lg-none" id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(2,2,7,0.65);z-index:1040;backdrop-filter:blur(4px);" onclick="toggleSidebar()"></div>
