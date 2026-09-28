@extends('errors.layout')
@section('code', ($exception?->getStatusCode() ?? 400))
@section('title', 'This path is unavailable.')
@section('label', 'REQUEST ERROR')
@section('message')
    {{ 'We could not complete this request. Head home and try another path.' }}
@endsection
