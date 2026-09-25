@php
    $groups = [
        'Explore' => ['Fandoms' => route('public.explore'), 'Characters' => route('public.section', 'characters'), 'Upcoming' => route('public.section', 'upcoming'), 'Merchandise' => route('public.section', 'merchandise'), 'Events' => route('events.index'), 'Live Chat' => auth()->check() ? route('chat.index') : route('login')],
        'Your space' => auth()->check() ? ['Dashboard' => route('public.account', 'dashboard'), 'Bookmarks' => route('public.account', 'bookmarks'), 'Profile' => route('profile.edit')] : ['Login' => route('login'), 'Create account' => route('register'), 'Bookmarks' => route('public.account', 'bookmarks')],
        'FanHub Plus' => ['Feedback' => route('public.section', 'feedback'), 'Privacy' => route('public.section', 'privacy'), 'Terms' => route('public.section', 'terms'), 'Sitemap' => route('public.sitemap')],
    ];
@endphp
<footer class="fh-footer-lite">
    <div class="fh-footer-lite__main">
        <div class="fh-footer-lite__brand">
            <x-site-brand />
            <p>Your favorite worlds. Your kind of people.<br>Find your next obsession with FanHub Plus.</p>
            <a class="fh-footer-lite__explore" href="{{ route('public.explore') }}">Find your fandom <x-site-icon name="arrow" /></a>
        </div>
        @foreach($groups as $title => $links)
            <nav aria-label="Footer {{ $title }}">
                <h2>{{ $title }}</h2>
                <ul>@foreach($links as $label => $href)<li><a href="{{ $href }}">{{ $label }}</a></li>@endforeach</ul>
            </nav>
        @endforeach
    </div>
    <div class="fh-footer-lite__bottom">
        <p>&copy; {{ date('Y') }} FanHub Plus. All rights reserved.</p>
        <div class="fh-socials" aria-label="Social media">
            @foreach(['discord' => 'Discord', 'twitter-x' => 'X', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $icon => $label)
                @if(config('socials.'.$icon))<a href="{{ config('socials.'.$icon) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></a>@endif
            @endforeach
        </div>
        <a href="#main-content" class="fh-footer-top">Back to top &uarr;</a>
    </div>
</footer>
