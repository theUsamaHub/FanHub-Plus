@extends('errors.layout')
@section('code', '403')
@section('title', 'This world is off limits.')
@section('label', 'ACCESS RESTRICTED')
@section('message')
    {{ 'You do not have permission to open this page. Head home to keep exploring.' }}
@endsection
