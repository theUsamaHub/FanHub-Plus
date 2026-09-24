<section id="featured-story" class="home-section home-featured" aria-labelledby="featured-heading">
    <div class="story-side-rail" aria-hidden="true"><b>FANHUB+</b><span>STORIES<br>PEOPLE<br>WORLDS<br>ALWAYS<br>TOGETHER</span><i></i><small>GOOD FANS<br>MAKE A<br>BRIGHTER<br>TOMORROW</small></div>
    <header class="home-section-heading" data-reveal="up">
        <p class="home-eyebrow"><span></span>FEATURED STORY<span></span></p>
        <h2 id="featured-heading">THE STORY <span>EVERYONE</span> IS TALKING ABOUT</h2>
        <p class="home-section-subtitle">Bigger worlds. Bolder stories. A fandom for what's next.</p>
    </header>
    <div class="home-section-toolbar"><a class="home-view-all" href="{{ route('public.explore', ['featured' => 1, 'type' => 'article']) }}">View All <x-site-icon name="arrow" /></a></div>
    @if($featuredStories->isNotEmpty())
        <div class="story-grid {{ $featuredStories->count() === 1 ? 'story-grid--single' : '' }}" data-stagger>
            @foreach($featuredStories as $story)<x-featured-story-card :story="$story" :primary="$loop->first" />@endforeach
        </div>
    @else
        <p class="home-empty">New worlds are waiting to be told. Featured stories will appear here soon.</p>
    @endif
    <div class="story-bottom-signoff" aria-hidden="true"><span>EXPLORE<br>CREATE<br>BELONG</span><p>FANHUB+<strong>FOR EVERY FAN. A BIGGER TOMORROW.</strong></p><span>FANDOM<br>LIVES<br>FURTHER +</span></div>
</section>
