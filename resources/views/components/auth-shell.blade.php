@props(['title', 'subtitle'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} · FanHubPlus</title>
@vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="fh-auth">
<header class="fh-auth-header"><x-site-brand /><a href="{{ route('home') }}" class="fh-auth-back">← Back to home</a></header>
<main class="fh-auth-shell">
<aside class="fh-auth-story" aria-label="Welcome to FanHubPlus">
<video class="fh-auth-art" autoplay muted loop playsinline preload="metadata" aria-hidden="true"><source src="{{ asset('videos/girl-behind-curtains-3.1920x1080.mp4') }}" type="video/mp4"></video>
<div class="fh-auth-story-content"><span class="fh-auth-eyebrow">YOUR NEXT CHAPTER STARTS HERE</span><h2>Every universe.<br>One <em>home.</em></h2><p>For the stories you love, the worlds you explore, and the fans who get it.</p><div class="fh-auth-tags"><span>Anime</span><span>Gaming</span><span>Cinema</span></div><div class="fh-auth-note">✦ &nbsp; A little more fandom. A lot more you.</div></div>
</aside>
<section class="fh-auth-panel" aria-labelledby="auth-title">
<nav class="fh-auth-tabs" aria-label="Account access"><a href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Log in</a><a href="{{ route('register') }}" @if(request()->routeIs('register')) aria-current="page" @endif>Sign up</a></nav>
<div class="fh-auth-heading"><span class="fh-auth-eyebrow">YOUR FANDOM. YOUR SPACE.</span><h1 id="auth-title">{{ $title }}</h1><p>{{ $subtitle }}</p></div>
{{ $slot }}
</section>
</main>
<footer class="fh-auth-footer">&copy; {{ date('Y') }} FanHubPlus <span>Made for fans. Built for belonging.</span></footer>
</body></html>
