@extends('layouts.public')
@php
    $activeSlug = $filters['category'] ?? '';
    $activeCategory = $categories->firstWhere('slug', $activeSlug);
    $identity = config('fandoms.'.$activeSlug, []);
    $pageTitle = $activeCategory?->name ?? ($identity['name'] ?? 'Explore');
    $landing = false;
@endphp
@section('title', $pageTitle.' | Fan Hub Plus')
@section('content')
    @include('public.partials.discovery')
@endsection
@push('styles')
    @vite('resources/js/pages/discovery.js')
@endpush
