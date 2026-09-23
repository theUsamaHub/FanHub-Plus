@extends('layouts.public')

@section('main-class', 'fh-home-stage')

@section('content')

    @include('home.sections.hero')

    @include('home.sections.fandoms')

    @include('home.sections.trending')

    @include('home.sections.featured-story')

    @include('home.sections.multimedia')

    @include('home.sections.characters')

    @include('home.sections.upcoming')

    @includeIf('home.sections.events')

    @include('home.sections.merchandise')

    @include('home.sections.join-cta')

@endsection
