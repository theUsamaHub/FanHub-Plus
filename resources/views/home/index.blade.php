@extends('layouts.public')

@section('main-class', 'fh-home-stage')

@push('styles')
    @vite('resources/js/pages/home.js')
@endpush

@section('content')
<div class="home-page" data-page="home" style="--home-dark-art: url('{{ asset(config('homepage.images.dark_background')) }}'); --home-light-art: url('{{ asset(config('homepage.images.light_background')) }}')">
    <h1 class="home-sr-only">Fan Hub Plus — Every Universe. One Home.</h1>

    @include('home.sections.hero')

    @include('home.sections.fandoms')

    @include('home.sections.trending')

    @include('home.sections.featured-story')
    // section for featured story is optional, so we use includeIf to avoid errors if the section is not defined

    @include('home.sections.multimedia')

    @include('home.sections.characters')

    @include('home.sections.upcoming')

    @includeIf('home.sections.events')

    @include('home.sections.merchandise')

    @include('home.sections.join-cta')
</div>
@endsection
