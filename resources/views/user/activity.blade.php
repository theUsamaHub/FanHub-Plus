@extends('user.layout', ['pageTitle' => 'Recent Activity'])
@section('member-content')
<header class="member-page-heading"><p>YOUR JOURNEY</p><h1>A trail of discoveries.</h1><span>Your reading, watching and collecting history, all in one place.</span></header>
<form class="member-filters" method="get"><label>Show activity<select name="type"><option value="">All activity</option>@foreach(['viewed', 'watched', 'saved', 'favorited', 'rated', 'reviewed', 'submitted'] as $type)<option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></label><button class="member-button">Apply filter</button></form>
<section class="member-panel member-activity-page">@include('user.partials.activity', ['items' => $activity])</section>
@include('user.partials.pagination', ['paginator' => $activity])
@endsection
