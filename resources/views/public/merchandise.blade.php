@extends('layouts.public')
@section('title', $merchandise->name.' | Fan Hub Plus')
@section('content')
<article class="fh-content-page">
    <a href="{{ route('public.section', 'upcoming') }}">← Upcoming releases</a>
    <p>{{ $merchandise->category?->name }} · {{ ucwords(str_replace('_', ' ', $merchandise->tag)) }}</p>
    <h1>{{ $merchandise->name }}</h1>
    <img class="fh-detail-image fh-detail-image--poster" src="{{ asset(config('homepage.images.upcoming')) }}" width="1382" height="2048" alt="">
    <p>{{ $merchandise->description }}</p>
    <p>{{ $merchandise->release_date ? 'Release date: '.$merchandise->release_date->format('F j, Y') : 'Release date to be announced.' }}</p>
</article>
@endsection
