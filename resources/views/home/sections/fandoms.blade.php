@php
    $fandomItems = $fandoms ?? \App\Models\Category::with('iconMedia')->orderBy('id')->get();
@endphp

<section id="fandoms" class="home-section explore-fandoms" data-explore-fandoms aria-labelledby="fandoms-title">
    <header class="explore-fandoms__heading">
        <p class="explore-fandoms__eyebrow"><span></span>CHOOSE YOUR UNIVERSE<span></span></p>
        <h2 id="fandoms-title">Explore <span>Fandoms</span></h2>
        <p>Anime, Gaming, Movies, K-Pop and more. Dive into the worlds you love.</p>
    </header>

    @if($fandomItems->isNotEmpty())
        <div class="fandom-cluster-wrapper">
            <div class="fandom-cluster" data-fandom-cluster aria-label="Explore fandoms">
                @foreach($fandomItems as $index => $fandom)
                    @php
                        $image = $fandom->iconMedia?->isImage() ? $fandom->iconMedia->url : null;
                        if (!$image) {
                            $image = match(strtolower($fandom->slug)) {
                                'anime', 'manga' => asset('images/fandoms/anime.png'),
                                'gaming', 'esports' => asset('images/fandoms/gaming.png'),
                                'movies', 'tv-shows', 'cinema' => asset('images/fandoms/cinema.png'),
                                default => asset('Anime.png'),
                            };
                        }
                        $iconMap = [
                            'anime' => 'torii',
                            'gaming' => 'controller',
                            'movies' => 'film',
                            'tv-shows' => 'tv',
                            'k-pop' => 'play',
                            'comics' => 'book',
                            'manga' => 'book',
                            'cosplay' => 'hanger',
                            'music' => 'play',
                            'esports' => 'crown',
                        ];
                        $icon = $iconMap[$fandom->slug] ?? 'compass';
                    @endphp
                    <a class="fandom-orbit" 
                       data-fandom-item 
                       data-index="{{ $index }}"
                       href="{{ route('public.explore', ['category' => $fandom->slug]) }}" 
                       aria-label="Explore {{ $fandom->name }}" 
                       aria-describedby="fandoms-hint">
                        <span class="fandom-orbit__circle">
                            <img src="{{ $image }}" 
                                 onerror="this.onerror=null;this.src='{{ asset('Anime.png') }}';" 
                                 alt="{{ $fandom->name }} fandom" 
                                 width="240" 
                                 height="240" 
                                 loading="lazy" 
                                 decoding="async">
                            <span class="fandom-orbit__overlay">
                                <x-site-icon :name="$icon" class="fandom-icon" />
                                <span class="fandom-orbit__name">{{ $fandom->name }}</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
        <button class="fandom-playback" type="button" data-fandom-playback aria-pressed="false" hidden>Pause motion</button>
    @else
        <p class="home-empty">New universes are on their way. Fandoms will appear here soon.</p>
    @endif
</section>
