<aside class="sidebar d-none d-lg-flex flex-column" id="sidebar">
    <div class="p-3 border-bottom border-secondary">
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none d-flex align-items-center">
            <x-application-logo class="w-8 h-8" />
            <span class="text-white fw-semibold ms-2 fs-6">{{ config('app.name', 'FanHubPlus') }}</span>
        </a>
    </div>

    <nav class="flex-grow-1 py-3 overflow-auto">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person"></i> {{ __('Profile') }}
                </a>
            </li>

            <li class="nav-item mt-2">
                <small class="text-uppercase text-secondary px-3 fw-semibold" style="font-size:0.7rem;letter-spacing:0.05em;">{{ __('Content') }}</small>
            </li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.contents.*') ? 'active' : '' }}" href="{{ route('admin.contents.index') }}"><i class="bi bi-file-earmark-text"></i> {{ __('Content') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}" href="{{ route('admin.submissions.index') }}"><i class="bi bi-inbox"></i> {{ __('Submissions') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.characters.*') ? 'active' : '' }}" href="{{ route('admin.characters.index') }}"><i class="bi bi-person-badge"></i> {{ __('Characters') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.merchandise.*') ? 'active' : '' }}" href="{{ route('admin.merchandise.index') }}"><i class="bi bi-box-seam"></i> {{ __('Merchandise') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}"><i class="bi bi-calendar-event"></i> {{ __('Events') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}"><i class="bi bi-chat-left-text"></i> {{ __('Reviews') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.ratings.*') ? 'active' : '' }}" href="{{ route('admin.ratings.index') }}"><i class="bi bi-star"></i> {{ __('Ratings') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}" href="{{ route('admin.feedback.index') }}"><i class="bi bi-megaphone"></i> {{ __('Feedback') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" href="{{ route('admin.analytics.index') }}"><i class="bi bi-graph-up"></i> {{ __('Analytics') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i> {{ __('Categories') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.categories.trashed') ? 'active' : '' }}" href="{{ route('admin.categories.trashed') }}"><i class="bi bi-trash"></i> {{ __('Recycle Bin') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" href="{{ route('admin.tags.index') }}"><i class="bi bi-bookmark"></i> {{ __('Tags') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}"><i class="bi bi-folder"></i> {{ __('Media') }}</a></li>

            <li class="nav-item mt-2">
                <small class="text-uppercase text-secondary px-3 fw-semibold" style="font-size:0.7rem;letter-spacing:0.05em;">{{ __('Users') }}</small>
            </li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> {{ __('Users') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-check"></i> {{ __('Roles') }}</a></li>
            <!-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}" href="{{ route('admin.contacts.index') }}"><i class="bi bi-envelope"></i> {{ __('Contacts') }}</a></li> -->
            <!-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}" href="{{ route('admin.subscribers.index') }}"><i class="bi bi-envelope-paper"></i> {{ __('Subscribers') }}</a></li> -->

            <li class="nav-item mt-2">
                <small class="text-uppercase text-secondary px-3 fw-semibold" style="font-size:0.7rem;letter-spacing:0.05em;">{{ __('System') }}</small>
            </li>
            <!-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear"></i> {{ __('Settings') }}</a></li> -->
            <!-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell"></i> {{ __('Notifications') }}</a></li> -->
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}"><i class="bi bi-clock-history"></i> {{ __('Activity') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}" href="{{ route('admin.sessions.index') }}"><i class="bi bi-person-badge"></i> {{ __('Sessions') }}</a></li>
            <!-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.ip-restrictions.*') ? 'active' : '' }}" href="{{ route('admin.ip-restrictions.index') }}"><i class="bi bi-shield-lock"></i> {{ __('IP Restrictions') }}</a></li> -->
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}" href="{{ route('admin.maintenance.index') }}"><i class="bi bi-shield-exclamation"></i> {{ __('Maintenance') }}</a></li>
            <!-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.health.*') ? 'active' : '' }}" href="{{ route('admin.health.index') }}"><i class="bi bi-heart-pulse"></i> {{ __('Health') }}</a></li> -->
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}"><i class="bi bi-journal-text"></i> {{ __('Logs') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.backup.*') ? 'active' : '' }}" href="{{ route('admin.backup.index') }}"><i class="bi bi-database"></i> {{ __('Backup') }}</a></li>
        </ul>
    </nav>

    <div class="p-3 border-top border-secondary">
        <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                <span class="text-white fw-semibold" style="font-size:0.875rem;">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div class="ms-2 overflow-hidden">
                <div class="text-white fw-medium text-truncate" style="font-size:0.875rem;">{{ Auth::user()->name }}</div>
                <div class="text-secondary text-truncate" style="font-size:0.75rem;">{{ __('Admin') }}</div>
            </div>
        </div>
    </div>
</aside>

<div class="sidebar-overlay d-lg-none" id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1040;" onclick="toggleSidebar()"></div>
