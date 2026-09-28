@extends('errors.layout')
@section('code', '503')
@section('title', 'Every universe needs a pause.')
@section('label', 'TEMPORARILY UNAVAILABLE')
@section('message')
    {{ $message ?? 'We are taking a short maintenance break. Please check back shortly.' }}
@endsection
