<div id="release-results" data-release-results>
    @if($releases->isNotEmpty())
        <div class="release-timeline" role="region" aria-label="Upcoming releases timeline" tabindex="0" data-release-track data-lenis-prevent>
            <div class="release-track" data-stagger>
                <span class="release-timeline-line" aria-hidden="true"><span data-timeline-progress></span></span>
                @foreach($releases as $release)<x-release-card :release="$release" />@endforeach
            </div>
        </div>
    @else
        <p class="home-empty">No upcoming releases announced for this fandom yet. Check back soon.</p>
    @endif
    <p class="home-sr-only" data-release-count>{{ $releases->count() }} upcoming {{ Str::plural('release', $releases->count()) }} shown.</p>
</div>
