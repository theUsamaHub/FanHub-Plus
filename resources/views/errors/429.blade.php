@extends('errors.layout')
@section('code', '429')
@section('title', 'A little breather.')
@section('label', 'TOO MANY REQUESTS')
@section('message')
    {{ 'You have made too many requests in a short time. Please wait a moment before trying again.' }}
@endsection
