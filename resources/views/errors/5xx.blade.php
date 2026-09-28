@extends('errors.layout')
@section('code', ($exception?->getStatusCode() ?? 500))
@section('title', 'We hit a cosmic detour.')
@section('label', 'SERVER ERROR')
@section('message')
    {{ 'The site is temporarily unable to complete your request. Please try again later.' }}
@endsection
