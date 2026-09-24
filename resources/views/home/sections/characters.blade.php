<section id="characters" class="character-spotlight" data-character-spotlight aria-labelledby="character-spotlight-title">
    <picture class="character-spotlight__backdrop character-spotlight__backdrop--dark" aria-hidden="true">
        <img src="{{ asset(config('homepage.images.characters_dark')) }}" alt="" loading="lazy" decoding="async" width="1672" height="941">
    </picture>
    <picture class="character-spotlight__backdrop character-spotlight__backdrop--light" aria-hidden="true">
        <img src="{{ asset(config('homepage.images.characters_light')) }}" alt="" loading="lazy" decoding="async" width="1672" height="941">
    </picture>
    <header class="character-spotlight__heading">
        <p class="character-spotlight__eyebrow"><span aria-hidden="true"></span>CHARACTER SPOTLIGHT<span aria-hidden="true"></span></p>
        <h2 id="character-spotlight-title">MEET THE <span>ICONS</span></h2>
        <p class="character-spotlight__subtitle">Stories change. Icons remain.</p>
    </header>

    @if($characters->isNotEmpty())
        <div class="character-spotlight__stage">
            <div class="character-spotlight__carousel swiper" data-character-carousel role="region" aria-roledescription="carousel" aria-label="Meet the icons">
                <div class="swiper-wrapper">
                    @foreach($characters as $character)
                        <div class="character-spotlight__slide swiper-slide">
                            <a class="character-card" href="{{ route('public.character', $character->slug) }}" aria-label="View character: {{ $character->name }}">
                                <img class="character-card__image" src="{{ $character->artwork_url }}" data-image-fallback="{{ asset(config('homepage.images.character')) }}" alt="{{ $character->name }}" width="474" height="843" loading="lazy" decoding="async">
                                <div class="character-card__copy">
                                    <p>{{ $character->category?->name ?? 'Fandom' }}</p>
                                    <h3>{{ $character->name }}</h3>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="character-spotlight__controls" data-character-controls hidden>
            <button type="button" class="character-spotlight__arrow" data-character-prev aria-label="Previous character">←</button>
            <div class="character-spotlight__pagination" data-character-pagination></div>
            <button type="button" class="character-spotlight__arrow" data-character-next aria-label="Next character">→</button>
            <button type="button" class="character-spotlight__playback" data-character-playback aria-label="Pause character autoplay" aria-pressed="false">Ⅱ</button>
        </div>
    @else
        <p class="character-spotlight__empty">Character spotlights will appear here soon.</p>
    @endif
</section>
