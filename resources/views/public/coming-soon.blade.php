@extends('layouts.public')
@section('title', $title.' | Fan Hub Plus')
@section('content')
<section class="fh-content-page">
    <h1>{{ $title }}</h1>
    <p>This part of Fan Hub Plus is coming soon.</p>
    <a href="{{ route('public.explore') }}" class="fh-button">Explore fandoms</a>
</section>
@endsection
