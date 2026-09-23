@php
    $groups = [
        ['title' => 'Explore', 'icon' => 'compass', 'links' => [
            'Home' => route('home'), 'Trending' => route('public.explore', ['sort' => 'popular']),
            'Featured' => route('public.explore', ['featured' => 1]), 'Characters' => route('public.section', 'characters'),
        ]],
        ['title' => 'Fandoms', 'icon' => 'controller', 'links' => collect(config('fandoms'))->mapWithKeys(fn ($fandom, $slug) => [$fandom['name'] => route('public.explore', ['category' => $slug])])->all()],
        ['title' => 'Discover', 'icon' => 'play', 'links' => [
            'Multimedia' => route('public.section', 'multimedia'), 'Events' => route('public.section', 'events'),
            'Upcoming' => route('public.section', 'upcoming'), 'Merchandise' => route('public.section', 'merchandise'),
        ]],
        ['title' => 'Account', 'icon' => 'user', 'links' => auth()->check() ? [
            'Profile' => route('profile.edit'), 'Submit Content' => route('public.account', 'submit-content'),
            'Dashboard' => route('public.account', 'dashboard'), 'Bookmarks' => route('public.account', 'bookmarks'),
        ] : [
            'Login' => route('login'), 'Register' => route('register'),
            'Dashboard' => route('public.account', 'dashboard'), 'Bookmarks' => route('public.account', 'bookmarks'),
        ]],
        ['title' => 'Other', 'icon' => 'settings', 'links' => [
            'Feedback' => route('public.section', 'feedback'), 'Privacy' => route('public.section', 'privacy'),
            'Terms' => route('public.section', 'terms'), 'Sitemap' => route('public.sitemap'),
        ]],
    ];
@endphp
<footer class="fh-footer">
    <div class="fh-footer-panel">
        <section class="fh-footer-brand" aria-label="About Fan Hub Plus" style="--footer-art: url('{{ asset('storage/images/footer.png') }}')">
            <span class="fh-footer-orbit" aria-hidden="true"></span>
            <x-site-brand />
            <p class="fh-footer-tagline">EVERY UNIVERSE. ONE HOME.</p>
            <span class="fh-footer-rule" aria-hidden="true"></span>
            <div class="fh-footer-message"><p>Fandoms. Stories. People. Possibilities.<br>Fan Hub Plus brings every universe<br class="fh-desktop-break"> closer, all in one home.</p><p class="fh-footer-motto">DIFFERENT<br> WORLDS.<br> A BRIGHTER<br> YOU.<span></span></p></div>
            <p class="fh-footer-community">FANS <b>/</b> CREATE <b>/</b> CONNECT <b>/</b> BELONG</p>
        </section>
        <div class="fh-footer-links">
            @foreach($groups as $group)
                <nav class="fh-footer-column" aria-label="Footer {{ $group['title'] }}">
                    <span class="fh-footer-icon"><x-site-icon :name="$group['icon']" /></span>
                    <h2>{{ $group['title'] }}</h2>
                    <ul>@foreach($group['links'] as $label => $href)<li><a href="{{ $href }}">{{ $label }}</a></li>@endforeach</ul>
                </nav>
            @endforeach
        </div>
    </div>
    <div class="fh-footer-bottom">
        <p>© {{ date('Y') }} Fan Hub Plus. All rights reserved.</p>
        <p class="fh-footer-signoff"><span></span>FANDOMS TODAY. BRIGHTER TOMORROW.<span></span></p>
        <div class="fh-socials" aria-label="Social media">
            @foreach(['discord' => 'Discord', 'twitter-x' => 'X', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $icon => $label)
                {{-- Configure official URLs when available; do not send fans to unrelated profiles. --}}
                @if(config('socials.'.$icon))
                    <a href="{{ config('socials.'.$icon) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></a>
                @else
                    <span class="fh-social-pending" role="img" aria-label="{{ $label }} — coming soon" title="{{ $label }} — coming soon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span>
                @endif
            @endforeach
        </div>
    </div>
</footer>
