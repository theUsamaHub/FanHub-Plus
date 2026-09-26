@extends('user.layout', ['pageTitle' => 'Bookmarks'])
@inject('library', 'App\Services\MemberLibrary')
@section('member-content')
<header class="member-page-heading"><p>YOUR PERSONAL COLLECTION</p><h1>Worth coming back to.</h1><span>{{ $bookmarks->total() }} saved items. A little space for everything you love.</span></header>
<form class="member-filters" method="get"><label>Content type<select name="type"><option value="">Everything</option>@foreach(['content' => 'Stories & media', 'character' => 'Characters', 'merchandise' => 'Merchandise', 'event' => 'Events', 'release' => 'Releases'] as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label><button class="member-button">Filter collection</button></form>
<div class="member-collection-grid">
@forelse($bookmarks as $bookmark)
<article class="member-panel member-bookmark">@if($card = $library->card($bookmark->bookmarkable))<x-member-card :card="$card" />@else<div class="member-empty"><h2>Item unavailable</h2><p>This item is no longer public. Your note is still private and available below.</p></div>@endif
<form class="member-form" action="{{ route('user.bookmarks.note', $bookmark) }}" method="post">@csrf @method('PATCH')<label for="note-{{ $bookmark->id }}">Private note<textarea id="note-{{ $bookmark->id }}" name="note" rows="2" maxlength="2000" placeholder="What made this worth saving?">{{ old('note', $bookmark->note) }}</textarea></label><button class="member-button member-button--quiet">Save note</button></form>
<form method="post" action="{{ route('user.bookmarks.destroy', $bookmark) }}">@csrf @method('DELETE')<button class="member-text-button">Remove bookmark</button></form></article>
@empty<div class="member-panel member-empty"><h2>Your bookshelf is waiting.</h2><p>Bookmark stories, characters and discoveries to keep them close.</p><a class="member-button" href="{{ route('public.explore') }}">Explore fandoms →</a></div>@endforelse
</div>
@include('user.partials.pagination', ['paginator' => $bookmarks])
@endsection
