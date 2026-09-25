<aside class="fh-adm-sidebar d-none d-lg-flex" id="sidebar">
    <div class="fh-adm-sidebar-sticky">
    <button type="button" class="fh-adm-sidebar-toggle" onclick="toggleSidebarCollapse()" aria-label="{{ __('Collapse sidebar') }}" title="{{ __('Collapse sidebar') }}">
        <i class="bi bi-chevron-double-left"></i>
    </button>
    <a class="fh-adm-brand" href="{{ route('admin.dashboard') }}">
        <x-site-icon name="crown" class="fh-brand__crown fh-adm-brand-mark" />
        <div class="fh-adm-brand-copy">
            <div class="fh-brand fh-adm-brand-word">
                <span>FAN<span class="fh-brand__accent">HUB+</span></span>
            </div>
            <span class="fh-adm-brand-sub">{{ __('Guild Command') }}</span>
        </div>
    </a>

    <nav class="fh-adm-nav" aria-label="{{ __('Admin navigation') }}">
        <div class="fh-adm-nav-group">{{ __('Overview') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}">
            <i class="bi bi-speedometer2"></i> <span>{{ __('Dashboard') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.health.index') ? 'is-active' : '' }}" href="{{ route('admin.health.index') }}" title="{{ __('Health') }}">
            <i class="bi bi-heart-pulse"></i> <span>{{ __('Health') }}</span>
        </a>

        <div class="fh-adm-nav-group">{{ __('Content') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.contents.*') ? 'is-active' : '' }}" href="{{ route('admin.contents.index') }}" title="{{ __('Content') }}">
            <i class="bi bi-file-earmark-richtext"></i> <span>{{ __('Content') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.submissions.*') ? 'is-active' : '' }}" href="{{ route('admin.submissions.index') }}" title="{{ __('Submissions') }}">
            <i class="bi bi-inbox-fill"></i> <span>{{ __('Submissions') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.categories.index') }}" title="{{ __('Categories') }}">
            <i class="bi bi-tag"></i> <span>{{ __('Categories') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.categories.trashed') ? 'is-active' : '' }}" href="{{ route('admin.categories.trashed') }}" title="{{ __('Recycle Bin') }}">
            <i class="bi bi-trash3"></i> <span>{{ __('Recycle Bin') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.tags.*') ? 'is-active' : '' }}" href="{{ route('admin.tags.index') }}" title="{{ __('Tags') }}">
            <i class="bi bi-bookmarks"></i> <span>{{ __('Tags') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}" href="{{ route('admin.media.index') }}" title="{{ __('Media') }}">
            <i class="bi bi-images"></i> <span>{{ __('Media') }}</span>
        </a>

        <div class="fh-adm-nav-group">{{ __('Community') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}" title="{{ __('Users') }}">
            <i class="bi bi-people-fill"></i> <span>{{ __('Users') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.roles.*') ? 'is-active' : '' }}" href="{{ route('admin.roles.index') }}" title="{{ __('Roles') }}">
            <i class="bi bi-shield-lock"></i> <span>{{ __('Roles') }}</span>
        </a>
        {{-- <a class="fh-adm-nav-link {{ request()->routeIs('admin.contacts.*') ? 'is-active' : '' }}" href="{{ route('admin.contacts.index') }}" title="{{ __('Contacts') }}">
            <i class="bi bi-envelope-open"></i> <span>{{ __('Contacts') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.subscribers.*') ? 'is-active' : '' }}" href="{{ route('admin.subscribers.index') }}" title="{{ __('Subscribers') }}">
            <i class="bi bi-person-lines-fill"></i> <span>{{ __('Subscribers') }}</span>
        </a> --}}
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}" href="{{ route('admin.reviews.index') }}" title="{{ __('Reviews') }}">
            <i class="bi bi-chat-square-text"></i> <span>{{ __('Reviews') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.ratings.*') ? 'is-active' : '' }}" href="{{ route('admin.ratings.index') }}" title="{{ __('Ratings') }}">
            <i class="bi bi-star-fill"></i> <span>{{ __('Ratings') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.feedback.*') ? 'is-active' : '' }}" href="{{ route('admin.feedback.index') }}" title="{{ __('Feedback') }}">
            <i class="bi bi-megaphone-fill"></i> <span>{{ __('Feedback') }}</span>
        </a>

        <div class="fh-adm-nav-group">{{ __('Engagement') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.characters.*') ? 'is-active' : '' }}" href="{{ route('admin.characters.index') }}" title="{{ __('Characters') }}">
            <i class="bi bi-person-vcard"></i> <span>{{ __('Characters') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.events.*') ? 'is-active' : '' }}" href="{{ route('admin.events.index') }}" title="{{ __('Events') }}">
            <i class="bi bi-calendar3"></i> <span>{{ __('Events') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.upcoming-releases.*') ? 'is-active' : '' }}" href="{{ route('admin.upcoming-releases.index') }}" title="{{ __('Upcoming Releases') }}">
            <i class="bi bi-rocket-takeoff"></i> <span>{{ __('Upcoming Releases') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.merchandise.*') ? 'is-active' : '' }}" href="{{ route('admin.merchandise.index') }}" title="{{ __('Merchandise') }}">
            <i class="bi bi-bag"></i> <span>{{ __('Merchandise') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.chatbot.*') ? 'is-active' : '' }}" href="{{ route('admin.chatbot.index') }}" title="{{ __('Chatbot') }}">
            <i class="bi bi-robot"></i> <span>{{ __('Chatbot') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.chatbot.faqs.*') ? 'is-active' : '' }}" href="{{ route('admin.chatbot.faqs.index') }}" title="{{ __('Chatbot FAQs') }}">
            <i class="bi bi-question-circle-fill"></i> <span>{{ __('Chatbot FAQs') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.analytics.*') ? 'is-active' : '' }}" href="{{ route('admin.analytics.index') }}" title="{{ __('Analytics') }}">
            <i class="bi bi-bar-chart-fill"></i> <span>{{ __('Analytics') }}</span>
        </a>

        <div class="fh-adm-nav-group">{{ __('System') }}</div>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.index') }}" title="{{ __('Settings') }}">
            <i class="bi bi-gear"></i> <span>{{ __('Settings') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.activity-logs.index') }}" title="{{ __('Activity Logs') }}">
            <i class="bi bi-clock-history"></i> <span>{{ __('Activity Logs') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.notifications.*') ? 'is-active' : '' }}" href="{{ route('admin.notifications.index') }}" title="{{ __('Notifications') }}">
            <i class="bi bi-bell"></i> <span>{{ __('Notifications') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.sessions.*') ? 'is-active' : '' }}" href="{{ route('admin.sessions.index') }}" title="{{ __('Sessions') }}">
            <i class="bi bi-box-arrow-in-right"></i> <span>{{ __('Sessions') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.ip-restrictions.*') ? 'is-active' : '' }}" href="{{ route('admin.ip-restrictions.index') }}" title="{{ __('IP Restrictions') }}">
            <i class="bi bi-shield-slash"></i> <span>{{ __('IP Restrictions') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.maintenance.*') ? 'is-active' : '' }}" href="{{ route('admin.maintenance.index') }}" title="{{ __('Maintenance') }}">
            <i class="bi bi-tools"></i> <span>{{ __('Maintenance') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.logs.*') ? 'is-active' : '' }}" href="{{ route('admin.logs.index') }}" title="{{ __('Logs') }}">
            <i class="bi bi-file-earmark-text"></i> <span>{{ __('Logs') }}</span>
        </a>
        <a class="fh-adm-nav-link {{ request()->routeIs('admin.backup.*') ? 'is-active' : '' }}" href="{{ route('admin.backup.index') }}" title="{{ __('Backups') }}">
            <i class="bi bi-hdd-stack"></i> <span>{{ __('Backups') }}</span>
        </a>
    </nav>

    <x-sidebar-spirit />

    <div class="fh-adm-sidebar-foot">
        <div class="fh-adm-avatar">
            @if (Auth::user()->profile?->avatarMedia?->url)
                <img src="{{ Auth::user()->profile->avatarMedia->url }}" alt="">
            @else
                {{ substr(Auth::user()->name, 0, 1) }}
            @endif
        </div>
        <div class="overflow-hidden">
            <div class="fh-adm-user-name">{{ Auth::user()->name }}</div>
            <div class="fh-adm-user-role">{{ __('Admin') }}</div>
        </div>
    </div>
    </div>
</aside>

<div class="sidebar-overlay d-lg-none" id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(2,2,7,0.65);z-index:1040;backdrop-filter:blur(4px);" onclick="toggleSidebar()"></div>
