@extends('layouts.public')
@section('title', $upcoming_release->title . ' | Upcoming | Fan Hub Plus')

@push('styles')
    <style>
        .urs-hero { position: relative; overflow: hidden; border: 1px solid var(--fh-line); border-radius: 20px; background: var(--fh-surface); }
        .urs-hero__art { position: relative; aspect-ratio: 21 / 9; overflow: hidden; background: #0c0a18; }
        .urs-hero__art img { width: 100%; height: 100%; object-fit: cover; object-position: center 30%; }
        .urs-hero__art::after { content: ''; position: absolute; inset: 0; background: linear-gradient(transparent 35%, var(--fh-surface) 96%); }
        .urs-hero__body { padding: 8px 28px 30px; margin-top: -70px; position: relative; z-index: 1; }
        .urs-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border: 1px solid #c5756555; border-radius: 999px; background: #c5756518; color: #f0b9a8; font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        :root[data-theme='light'] .urs-chip { color: #a8482e; }
        .urs-hero h1 { margin: 14px 0 8px; font-family: 'Rajdhani', sans-serif; font-size: clamp(30px, 5vw, 52px); line-height: 1.1; }
        .urs-meta { display: flex; flex-wrap: wrap; gap: 12px 22px; color: var(--fh-muted); font-size: 14px; margin: 12px 0 0; }
        .urs-meta strong { color: var(--fh-text); }
        .urs-desc { max-width: 760px; margin: 22px 0 0; color: var(--fh-muted); line-height: 1.85; white-space: pre-line; }
        .urs-back { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 22px; color: var(--fh-accent); font-size: 14px; font-weight: 600; }
        .urs-back .fh-icon { width: 16px; height: 16px; transform: rotate(180deg); }
    </style>
@endpush

@section('content')
<section class="fh-content-page">
    <a class="urs-back" href="{{ route('public.section', ['section' => 'upcoming']) }}"><x-site-icon name="arrow" /> Back to Upcoming</a>

    <article class="urs-hero">
        <div class="urs-hero__art">
            <img src="{{ $upcoming_release->artwork_url }}" alt="" width="1200" height="514" loading="eager" decoding="async">
        </div>
        <div class="urs-hero__body">
            <span class="urs-chip">{{ $upcoming_release->kind_label }} · {{ $upcoming_release->category?->name ?? 'Fandom' }}</span>
            <h1>{{ $upcoming_release->title }}</h1>
            <div class="urs-meta">
                @if($upcoming_release->release_date)
                    <span><strong>{{ $upcoming_release->release_date->format('M d, Y') }}</strong> release</span>
                @else
                    <span><strong>Date TBA</strong></span>
                @endif
                @if($upcoming_release->release_label)
                    <span>{{ $upcoming_release->release_label }}</span>
                @endif
            </div>
            @if($upcoming_release->description)
                <p class="urs-desc">{{ $upcoming_release->description }}</p>
            @endif
        </div>
    </article>
</section>
@endsection
