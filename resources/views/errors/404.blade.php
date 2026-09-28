@extends('errors.layout')
@section('code', '404')
@section('title', 'Lost between universes?')
@section('label', 'PAGE NOT FOUND')
@section('message')
    {{ 'This page may have moved, or the link may be out of date. Your next discovery is still waiting.' }}
@endsection
