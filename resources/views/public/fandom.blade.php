@extends('layouts.public')
@php
    $activeSlug = $category->slug;
    $activeCategory = $category;
    $identity = $fandomConfig;
    $pageTitle = $category->name;
    $landing = true;
@endphp
@section('title', $pageTitle.' | Fan Hub Plus')
@section('content')
    @include('public.partials.discovery')
@endsection
@push('styles')
    @vite('resources/js/pages/discovery.js')
@endpush
