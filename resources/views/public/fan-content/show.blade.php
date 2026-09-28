@extends('layouts.public')
@section('title', $content->title.' | Fan Content')
@section('content')
<div class="fan-list fan-list--reading"><a class="fan-list__breadcrumb" href="{{ route('public.fan-content.index') }}">← All fan content</a>@include('public.fan-content.detail')</div>
@endsection
