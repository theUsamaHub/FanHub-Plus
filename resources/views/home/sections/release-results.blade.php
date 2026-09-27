<div id="release-results" data-release-results>
    @if($releases->isNotEmpty())
        <div class="swiper release-swiper" role="region" aria-label="Upcoming releases" data-release-swiper>
            <div class="swiper-wrapper" data-stagger>
                @foreach($releases as $release)
                    <div class="swiper-slide release-slide"><x-release-card :release="$release" /></div>
                @endforeach
            </div>
        </div>
    @else
        <p class="home-empty">No upcoming releases announced for this fandom yet. Check back soon.</p>
    @endif
    <p class="home-sr-only" data-release-count>{{ $releases->count() }} upcoming {{ Str::plural('release', $releases->count()) }} shown.</p>
</div>
