<header class="fh-header" data-site-header>
    <nav class="fh-nav" aria-label="Main navigation">
        <x-site-brand />
        <button class="fh-icon-button fh-mobile-toggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="site-navigation" data-mobile-toggle><x-site-icon name="menu" /></button>
        <div class="fh-nav-links" id="site-navigation" data-navigation>
            <a class="fh-nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            <div class="fh-fandoms" data-fandoms>
                <button class="fh-nav-link fh-fandom-trigger {{ request()->routeIs('public.explore') ? 'is-active' : '' }}" type="button" aria-expanded="false" aria-controls="fandom-menu" data-fandom-toggle>Fandoms <x-site-icon name="chevron" /></button>
                <div class="fh-mega" id="fandom-menu" data-fandom-menu hidden>
                    <div class="fh-mega-heading"><span>EXPLORE FANDOMS</span><a href="{{ route('public.explore') }}">View all &rarr;</a></div>
                    <div class="fh-fandom-grid">
                    @foreach(config('fandoms') as $slug => $fandom)
                        <a class="fh-fandom-card fh-fandom-card--{{ $slug }}" href="{{ route('public.explore', ['category' => $slug]) }}">
                            <x-site-icon :name="$fandom['icon']" /><span>{{ $fandom['name'] }}</span><x-site-icon name="next" />
                        </a>
                    @endforeach
                    </div>
                </div>
            </div>
            @foreach(['characters' => 'Characters', 'upcoming' => 'Upcoming', 'merchandise' => 'Merchandise'] as $slug => $label)
                <a class="fh-nav-link {{ request()->is('discover/'.$slug) ? 'is-active' : '' }}" href="{{ route('public.section', $slug) }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="fh-nav-actions">
            <button class="fh-icon-button fh-search-toggle" type="button" aria-label="Search Fan Hub Plus" data-search-open><x-site-icon name="search" /><span>Find your fandom...</span></button>
            <span class="fh-action-divider" aria-hidden="true"></span>
            <button class="fh-theme-toggle" type="button" role="switch" aria-checked="true" aria-label="Dark mode" data-theme-toggle><x-site-icon name="sun" /></button>
            @auth
                <div class="fh-account" data-account>
                    <button class="fh-login fh-avatar-button" type="button" aria-expanded="false" aria-controls="account-menu" aria-label="Open account menu" data-account-toggle><span class="fh-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="fh-account-name">{{ auth()->user()->name }}</span><x-site-icon name="chevron" /></button>
                    <div class="fh-account-menu" id="account-menu" hidden>
                        <p>Welcome, {{ auth()->user()->name }}</p>
                        <a href="{{ route('public.account', 'dashboard') }}">My Dashboard</a>
                        <a href="{{ route('public.account', 'bookmarks') }}">Bookmarks</a>
                        <a href="{{ route('profile.edit') }}">Profile</a>
                        <a href="{{ route('public.account', 'submit-content') }}">Submit Content</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
                    </div>
                </div>
            @else
                <a class="fh-login" href="{{ route('login') }}" aria-label="Login or sign up"><x-site-icon name="user" /><span>Login / Register</span></a>
            @endauth
        </div>
    </nav>
</header>
<dialog class="fh-search-dialog" data-search-dialog aria-labelledby="search-title">
    <button class="fh-icon-button fh-dialog-close" type="button" aria-label="Close search" data-search-close><x-site-icon name="close" /></button>
    <h2 id="search-title">Find your universe</h2>
    <p>Search stories across all eight fandoms.</p>
    <form action="{{ route('public.explore') }}" method="GET" role="search">
        <label for="site-search">Search Fan Hub Plus</label>
        <div class="fh-search-field"><input id="site-search" name="q" type="search" maxlength="120" placeholder="Anime, games, stories..." required><button type="submit" class="fh-button">Search</button></div>
    </form>
    @guest <p class="fh-search-join">New here? <a href="{{ route('register') }}">Join FanHub+</a></p> @endguest
</dialog>
