@extends('errors.layout')
@section('code', '419')
@section('title', 'Time for a fresh start.')
@section('label', 'SESSION EXPIRED')
@section('message')
    {{ 'Your session has expired. Return to the page and try again. Your last submission may not have been saved.' }}
@endsection
