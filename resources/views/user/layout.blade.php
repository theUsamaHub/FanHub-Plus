@extends('layouts.public')
@section('title', ($pageTitle ?? 'Your space').' | FanHub Plus')
@section('main-class', 'member-main')
@section('content')
<div class="member-space">
    @unless(request()->routeIs('user.dashboard'))
    <nav class="member-nav" aria-label="Your account">
        @foreach(['user.dashboard' => 'Overview', 'user.bookmarks' => 'Bookmarks', 'user.favorites' => 'Fandoms', 'user.activity' => 'Activity', 'user.submissions' => 'My submissions', 'user.reviews' => 'Reviews', 'profile.edit' => 'Settings', 'user.feedback' => 'Feedback'] as $link => $label)
        <a href="{{ route($link) }}" @if(request()->routeIs($link) || ($link === 'user.submissions' && request()->routeIs('user.submissions.*'))) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    @endunless
    @include('user.partials.messages')
    @yield('member-content')
</div>
@endsection
