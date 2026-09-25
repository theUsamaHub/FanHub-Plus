@extends('layouts.public')

@section('main-class', 'fh-home-stage')

@push('styles')
    @vite('resources/js/pages/home.js')
@endpush

@section('content')
<div class="home-page" data-page="home" style="--home-dark-art: url('{{ asset(config('homepage.images.dark_background')) }}'); --home-light-art: url('{{ asset(config('homepage.images.light_background')) }}')">

    @include('home.sections.hero')
    // img
  
    <div class="home-scroll-content">
    @include('home.sections.fandoms')

    @include('home.sections.trending')

    @include('home.sections.featured-story')

    @include('home.sections.multimedia')

    @include('home.sections.characters')

    @include('home.sections.upcoming')

    @include('home.sections.events')

    @include('home.sections.merchandise')

    @include('home.sections.join-cta')
    </div>
</div>
@endsection
