@extends('layouts.public')

@section('main-class', 'fh-home-stage')

@push('styles')
    @vite('resources/js/pages/home.js')
@endpush

@section('content')
<div class="home-page" data-page="home" style="--home-dark-art: url('{{ asset(config('homepage.images.dark_background')) }}'); --home-light-art: url('{{ asset(config('homepage.images.light_background')) }}')">

    @include('home.sections.hero')
   
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

<button class="home-scroll-top" type="button" data-scroll-top aria-label="Back to top" title="Back to top" hidden>
    <x-site-icon name="arrow" />
</button>

<!-- Content Detail Modal -->
@if(isset($featuredStories) && $featuredStories->isNotEmpty())
    <x-content-modal :content="$featuredStories->first()" />
@elseif(isset($trending) && $trending->isNotEmpty())
    <x-content-modal :content="$trending->first()" />
@else
    <x-content-modal />
@endif

<!-- Fan Content Modal (opens when clicking fan content tiles in the home Fan Content section) -->
@include('public.fan-content.modal')
@endsection
