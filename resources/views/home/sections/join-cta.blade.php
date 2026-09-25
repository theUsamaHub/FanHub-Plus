@guest
<section class="home-section join-section" aria-labelledby="join-title" data-join-section>
    <p class="join-label"><span></span><span class="fan-gradient-text">JOIN FANHUB PLUS</span><span></span></p>
    <h2 id="join-title">BE PART OF A<br><span class="fan-gradient-text">BIGGER UNIVERSE</span></h2>
    <p class="join-description">Join a growing community of fans, explore new worlds,<br class="join-desktop-break"> share your passion and never miss what's next.</p>
    <ul class="join-benefits">
        @foreach([
            ['icon' => 'people', 'title' => 'Connect', 'text' => 'with fellow fans'],
            ['icon' => 'book', 'title' => 'Discover', 'text' => 'new stories'],
            ['icon' => 'chat', 'title' => 'Share', 'text' => 'your passion'],
            ['icon' => 'heart', 'title' => 'Be Part', 'text' => 'of something bigger'],
        ] as $benefit)
            <li><span class="join-benefit-icon"><x-site-icon :name="$benefit['icon']" /></span><h3>{{ $benefit['title'] }}</h3><p>{{ $benefit['text'] }}</p></li>
        @endforeach
    </ul>
    <a class="join-button" href="{{ route('register') }}">Join the Community <x-site-icon name="arrow" /></a>
</section>
@endguest
