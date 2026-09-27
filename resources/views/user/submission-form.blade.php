@extends('user.layout', ['pageTitle' => $submission->exists ? 'Edit Submission' : 'New Submission'])
@section('member-content')
@php $hasCover = $submission->exists && $submission->media->contains(fn ($media) => $media->pivot?->role === 'cover'); @endphp
<header class="member-page-heading"><p>THE COMMUNITY DESK</p><h1>{{ $submission->exists ? 'Give it another chapter.' : 'Something worth sharing.' }}</h1><span>Bring your perspective, artwork or discoveries to the community.</span></header>
<div class="member-editor-layout"><form class="member-panel member-form" action="{{ $submission->exists ? route('user.submissions.update', $submission) : route('user.submissions.store') }}" method="post" enctype="multipart/form-data">@csrf @if($submission->exists) @method('PUT') @endif
@if($errors->any()) @foreach($errors->all() as $error)<p role="alert">{{ $error }}</p> @endforeach @endif
<label>Title<input name="title" value="{{ old('title', $submission->title) }}" maxlength="180" required placeholder="Give your contribution a title"></label>
<label>Fandom<select name="category_id" required><option value="">Choose a fandom</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $submission->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
<label>Short introduction<textarea name="excerpt" maxlength="500" rows="2" placeholder="A little context for your readers…">{{ old('excerpt', $submission->excerpt) }}</textarea></label>
<label>Your story / description<textarea name="body" rows="12" minlength="30" maxlength="50000" required placeholder="Tell your story in your own words…">{{ old('body', $submission->body) }}</textarea></label>
<label>Cover image <small>{{ $hasCover ? 'Leave empty to keep your current cover · JPG, PNG, WebP, GIF · up to 5 MB' : 'Required · JPG, PNG, WebP, GIF · up to 5 MB' }}</small><input type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/gif" {{ $hasCover ? '' : 'required' }}></label>
@if($hasCover)<p class="member-muted">Current cover: {{ $submission->media->first(fn ($media) => $media->pivot?->role === 'cover')?->original_filename }}. Leave the field empty to keep it.</p>@endif
<div class="member-actions"><button class="member-button" name="intent" value="submit">Submit for review →</button><button class="member-button member-button--quiet" name="intent" value="draft">Save draft</button><a href="{{ route('user.submissions') }}">Cancel</a></div>
</form><aside class="member-panel member-editor-note"><span class="member-kicker">BEFORE YOU SHARE</span><h2>A few thoughtful details.</h2><p>Share work you created or have permission to use. Credit your sources and keep the conversation welcoming.</p><p>Every submission needs a cover image so it looks right across the site.</p><p>You can save a draft and return later. Submitted work stays private until a moderator approves it.</p></aside></div>
@endsection
