<section class="fan-hero" aria-labelledby="fan-hero-title" data-video-hero>
    <video class="fan-hero__video" muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1">
        <source src="{{ asset('videos/sukuna-fuga-flames.mp4') }}" type="video/mp4">
    </video>
    <div class="fan-hero__shade" aria-hidden="true"></div>
    <div class="fan-hero__content">
        <p class="fan-hero__eyebrow"><span></span> WELCOME TO FANHUB PLUS</p>
        <h1 id="fan-hero-title">EVERY UNIVERSE.<br><span>ONE HOME.</span><br>YOUR FANDOM.</h1>
        <p class="fan-hero__description">Epic stories. Unforgettable characters. A world of fans like you. Discover anime, explore new universes, and find where you belong.</p>
        <div class="fan-hero__actions">
            <a class="fan-hero__primary" href="{{ route('public.explore', ['category' => 'anime']) }}"><x-site-icon name="compass" /> Explore Anime</a>
            <a class="fan-hero__secondary" href="{{ route('public.explore') }}">Explore Fandoms <x-site-icon name="arrow" /></a>
        </div>
        <p class="fan-hero__caption">YOUR NEXT OBSESSION STARTS HERE</p>
    </div>
    <button class="fan-hero__playback" type="button" aria-label="Play background video" aria-pressed="false" hidden>
        <span data-video-symbol aria-hidden="true">▶</span><span data-video-label>Play motion</span>
    </button>
    <div class="fan-hero__bottom">
        <div class="fan-hero__facts" aria-label="Discover FanHub Plus">
            <div><strong>{{ count(config('fandoms')) }}</strong><span>Fandom universes</span></div>
            <div><strong>Discover</strong><span>Stories &amp; characters</span></div>
            <div><strong>Connect</strong><span>Find your people</span></div>
        </div>
        <div class="fan-hero__picks" aria-label="Explore a fandom">
            @foreach(['anime' => ['01', 'torii', 'Anime', 'Find your next story'], 'manga' => ['02', 'book', 'Manga', 'A world on every page'], 'gaming' => ['03', 'controller', 'Gaming', 'Enter another world']] as $slug => $pick)
                <a href="{{ route('public.explore', ['category' => $slug]) }}" class="fan-hero__pick fan-hero__pick--{{ $slug }}">
                    <span class="fan-hero__pick-art" style="background-image: url('{{ asset('images/hero/fandom-cards.png') }}')" aria-hidden="true"></span>
                    <span class="fan-hero__pick-top"><x-site-icon :name="$pick[1]" /><span>{{ $pick[0] }}</span></span>
                    <strong>{{ $pick[2] }} <span aria-hidden="true">↗</span></strong><small>{{ $pick[3] }}</small>
                </a>
            @endforeach
        </div>
    </div>
</section>
