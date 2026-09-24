<section id="trending" class="home-section home-trending" aria-labelledby="trending-heading">
    <p class="home-side-note home-side-note--left" aria-hidden="true">DIFFERENT<br>WORLDS.<br>BOLDER<br>PEOPLE.</p>
    <p class="home-side-note home-side-note--right" aria-hidden="true">SAME<br>PASSION.<br>MORE<br>WORLDS.</p>
    <x-home-section-heading id="trending-heading" eyebrow="TRENDING NOW" title="TRENDING" accent="NOW" subtitle="The fandoms everyone is watching, reading, and talking about." />
    <div class="home-section-toolbar"><a class="home-view-all" href="{{ route('public.explore', ['sort' => 'popular']) }}">View All <x-site-icon name="arrow" /></a></div>
    @if($trending->isNotEmpty())
        <div class="trending-grid" data-stagger>
            @foreach($trending as $content)<x-trending-card :content="$content" :rank="$loop->iteration" />@endforeach
        </div>
    @else
        <p class="home-empty">Your next obsession is on its way. Trending stories will appear here as they are published.</p>
    @endif
</section>
