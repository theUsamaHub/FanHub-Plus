@extends('layouts.public')
@section('title', 'Sitemap | Fan Hub Plus')
@section('content')
<section class="fh-content-page">
    <h1>Sitemap</h1>
    <p>Find your way around Fan Hub Plus.</p>
    <div class="fh-sitemap">
        <nav aria-label="Explore pages"><h2>Explore</h2><ul><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('public.explore') }}">Explore</a></li><li><a href="{{ route('public.explore', ['sort' => 'popular']) }}">Trending</a></li><li><a href="{{ route('public.explore', ['featured' => 1]) }}">Featured</a></li>@foreach(\App\Http\Controllers\PublicSiteController::SECTIONS as $slug => $label)<li><a href="{{ route('public.section', $slug) }}">{{ $label }}</a></li>@endforeach</ul></nav>
        <nav aria-label="Fandom pages"><h2>Fandoms</h2><ul>@foreach(config('fandoms') as $slug => $fandom)<li><a href="{{ route('public.explore', ['category' => $slug]) }}">{{ $fandom['name'] }}</a></li>@endforeach</ul></nav>
        <nav aria-label="Account pages"><h2>Account</h2><ul>@guest<li><a href="{{ route('login') }}">Login</a></li><li><a href="{{ route('register') }}">Register</a></li>@endguest<li><a href="{{ route('public.account', 'dashboard') }}">My Dashboard</a></li><li><a href="{{ route('public.account', 'bookmarks') }}">Bookmarks</a></li><li><a href="{{ route('profile.edit') }}">Profile</a></li><li><a href="{{ route('public.account', 'submit-content') }}">Submit Content</a></li></ul></nav>
    </div>
    <nav aria-label="More in your space"><h2>Your space</h2><ul><li><a href="{{ route('user.favorites') }}">Favorite Fandoms</a></li><li><a href="{{ route('user.activity') }}">Recent Activity</a></li><li><a href="{{ route('user.submissions') }}">My Submissions</a></li><li><a href="{{ route('user.reviews') }}">My Reviews</a></li><li><a href="{{ route('user.feedback') }}">Feedback</a></li><li><a href="{{ route('events.nearby') }}">Events Near You</a></li></ul></nav>
</section>
@endsection
