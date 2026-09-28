@extends('errors.layout')
@section('code', '500')
@section('title', 'A twist we did not plan.')
@section('label', 'SERVER ERROR')
@section('message')
    {{ 'Something went wrong on our side. Please try again later.' }}
@endsection
