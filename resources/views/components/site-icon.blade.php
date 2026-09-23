@props(['name'])
<svg {{ $attributes->class('fh-icon') }} viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('crown') <path d="m3 8 7 6 6-12 6 12 7-6-3 17H6L3 8Z"/><path d="M7 27h18l-9 4-9-4" stroke="#219eff"/> @break
        @case('search') <circle cx="14" cy="14" r="9"/><path d="m21 21 7 7"/> @break
        @case('sun') <circle cx="16" cy="16" r="6"/><path d="M16 2v4m0 20v4M2 16h4m20 0h4M6 6l3 3m14 14 3 3M6 26l3-3M23 9l3-3"/> @break
        @case('moon') <path d="M23 4A12 12 0 1 1 8 23 12 12 0 0 0 23 4Z" fill="currentColor"/> @break
        @case('user') <circle cx="16" cy="10" r="6"/><path d="M4 29v-3a12 12 0 0 1 24 0v3Z"/> @break
        @case('menu') <path d="M5 8h22M5 16h22M5 24h22"/> @break
        @case('close') <path d="m8 8 16 16M24 8 8 24"/> @break
        @case('chevron') <path d="m8 12 8 8 8-8"/> @break
        @case('torii') <path d="M3 5q13 4 26 0M4 11h24M9 8v21M23 8v21M16 7v4M7 18h18"/> @break
        @case('controller') <path d="M10 7h12c4 0 5 5 7 15 1 5-3 7-6 3l-4-5h-6l-4 5c-3 4-7 2-6-3C5 12 6 7 10 7Z"/><path d="M8 13h6m-3-3v6m10-4h.1m4 4h.1M13 7l1-3h4l1 3"/> @break
        @case('film') <path d="m16 2 4 4 6 1 1 6 3 3-3 4-1 6-6 1-4 3-4-3-6-1-1-6-3-4 3-3 1-6 6-1Z"/><circle cx="16" cy="16" r="6"/> @break
        @case('tv') <rect x="3" y="9" width="26" height="21" rx="2"/><path d="m10 2 6 7 6-7M7 14h14v11H7zM25 15v2m0 5v2"/> @break
        @case('gem') <path d="m2 11 7-8h14l7 8-14 19L2 11Zm0 0h28M9 3l7 27L23 3M9 3l7 8 7-8"/> @break
        @case('mask') <path d="M2 12c6-5 9-1 14-1s8-4 14 1c1 10-10 15-14 6C12 27 1 22 2 12Z"/><path d="M7 14h4v3H7zm14 0h4v3h-4zM7 6l9-3 9 3"/> @break
        @case('book') <path d="M16 7C11 2 6 3 3 5v24c4-3 9-3 13 0 4-3 9-3 13 0V5c-3-2-8-3-13 2v22M7 10h4m-4 5h4m-4 5h4m10-10h4m-4 5h4m-4 5h4"/> @break
        @case('hanger') <path d="M13 7a3 3 0 1 1 3 3v5L3 25c-1 1-1 3 1 3h24c2 0 2-2 1-3L16 15"/> @break
        @case('compass') <circle cx="16" cy="16" r="14"/><path d="m22 9-4 11-9 4 4-11 9-4Z"/><path d="m13 13 5 7"/> @break
        @case('play') <path d="m8 3 21 13L8 29V3Z"/> @break
        @case('settings') <path d="m12 3 1 4h6l1-4 5 3-2 4 3 5 4 1v5l-4 1-3 5 2 3-5 2-1-4h-6l-1 4-5-2 2-3-3-5-4-1v-5l4-1 3-5-2-4 5-3Z" transform="translate(1 -2) scale(.95)"/><circle cx="16" cy="16" r="5"/> @break
    @endswitch
</svg>
