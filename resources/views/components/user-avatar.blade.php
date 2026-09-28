@props(['user'])
@php($avatar = $user->profile?->avatarMedia)
<span {{ $attributes->class(['user-avatar']) }} aria-hidden="true">
    <span>{{ mb_strtoupper(mb_substr($user->profile?->display_name ?: $user->name, 0, 1)) }}</span>
    @if($avatar?->isImage() && $avatar->hasValidPath())
        <img src="{{ $avatar->url }}" alt="" width="64" height="64" data-avatar-image>
    @endif
</span>
