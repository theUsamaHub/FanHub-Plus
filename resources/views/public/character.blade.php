@extends('layouts.public')
@php
    $fandomColor = $character->category?->slug 
        ? config("fandoms.{$character->category->slug}.color", '#ed9b68') 
        : '#ed9b68';
    $fandomGlow = $fandomColor . '26';
@endphp

<style>
    .character-page { --fandom-color: {{ $fandomColor }}; --fandom-glow: {{ $fandomGlow }}; }
    
    .character-hero { position: relative; isolation: isolate; min-height: 300px; display: flex; flex-direction: column; justify-content: flex-end; overflow: hidden; border: 1px solid #ffffff26; border-radius: 24px 24px 0 0; background: #18131f; color: #fff9f2; }
    .character-hero::before { content: ''; position: absolute; z-index: -1; inset: 0; background: linear-gradient(90deg,#120f1bf5 5%,#120f1bd4 38%,#120f1b30 85%),linear-gradient(0deg,#120f1bf2,transparent 55%); }
    .character-hero::after { content: ''; position: absolute; z-index: -1; inset: 0; background: radial-gradient(ellipse at 15% 100%,var(--fandom-glow),transparent 65%); }
    .character-hero__content { position: relative; z-index: 1; padding: clamp(24px,4vw,48px); max-width: 1440px; margin: 0 auto; width: 100%; }
    .character-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border: 1px solid rgba(255,255,255,0.15); border-radius: 999px; background: rgba(24,19,31,0.85); color: #fff9f2; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; backdrop-filter: blur(12px); }
    .character-badge .fh-icon { width: 16px; height: 16px; color: var(--fandom-color); }
    .character-name { font: 700 clamp(40px,6vw,96px)/.95 'Rajdhani',sans-serif; letter-spacing: -2.5px; margin: 16px 0 8px; overflow-wrap: anywhere; line-height: 1; }
    .character-name span { display: block; color: var(--fandom-color); font-size: 0.65em; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
    .character-meta { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1); }
    .character-meta-item { display: flex; align-items: center; gap: 8px; color: #dfd5e5; font-size: 12px; }
    .character-meta-item .fh-icon { width: 16px; height: 16px; color: var(--fandom-color); opacity: 0.8; }
    .character-meta-label { color: var(--fh-muted); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-right: 4px; }
    .character-meta-value { font-weight: 600; }

    /* LAYOUT - Split view like content page */
    .character-layout { display: grid; grid-template-columns: 380px minmax(0,1fr); gap: 60px; justify-content: center; padding-top: 48px; max-width: 1440px; margin: 0 auto; padding-left: clamp(24px,4vw,64px); padding-right: clamp(24px,4vw,64px); padding-bottom: 80px; }
    
    /* LEFT SIDE - Character Cover/Artwork */
    .character-cover { position: sticky; top: 120px; max-height: calc(100vh - 200px); border-radius: 20px; overflow: hidden; background: var(--fh-surface); border: 1px solid var(--fh-line); }
    .character-cover img { display: block; width: 100%; height: auto; max-height: 800px; object-fit: cover; }
    
    /* RIGHT SIDE - Content */
    .character-prose { max-width: 760px; }
    
    .character-section { margin-bottom: 60px; }
    .character-section:last-child { margin-bottom: 0; }
    
    .character-section-header { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--fh-line); }
    .character-section-title-group { display: flex; flex-direction: column; gap: 8px; }
    .character-section-eyebrow { color: var(--fandom-color); font-size: 11px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; }
    .character-section-title { font: 600 clamp(28px,3.5vw,40px)/1.1 'Rajdhani',sans-serif; margin: 0; letter-spacing: -1px; }
    .character-section-title em { color: var(--fh-accent); font-style: normal; }
    .character-section-view-all { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1px solid var(--fh-line); border-radius: 8px; color: var(--fh-muted); font-size: 12px; font-weight: 500; transition: border-color .25s, color .25s, background .25s; }
    .character-section-view-all:hover { border-color: var(--fandom-color); color: var(--fandom-color); background: var(--fandom-glow); }
    .character-section-view-all .fh-icon { width: 16px; height: 16px; transition: transform .25s; }
    .character-section-view-all:hover .fh-icon { transform: translateX(3px); }

    /* BIO CARD */
    .character-bio-card { background: var(--fh-surface); border: 1px solid var(--fh-line); border-radius: 20px; overflow: hidden; }
    .character-bio-content { padding: 32px 40px; }
    .character-bio-content .fandom-reading__body--rich { font-size: 16px; line-height: 1.95; color: var(--fh-muted); }
    .character-bio-content .fandom-reading__body--rich p { margin-bottom: 1.5em; }
    .character-bio-content .fandom-reading__body--rich h2,
    .character-bio-content .fandom-reading__body--rich h3 { color: var(--fh-text); margin: 1.5em 0 0.5em; font-family: 'Rajdhani',sans-serif; font-weight: 700; }
    .character-bio-content .fandom-reading__body--rich strong { color: var(--fh-text); }
    .character-bio-content .fandom-reading__body--rich a { color: var(--fandom-color); text-decoration: underline; }
    .character-bio-content .fandom-reading__body--rich ul { margin: 1em 0; padding-left: 1.5em; }
    .character-bio-content .fandom-reading__body--rich li { margin-bottom: 0.5em; }
    .character-bio-empty { text-align: center; padding: 60px 24px; }
    .character-bio-empty .fh-icon { width: 56px; height: 56px; color: var(--fh-accent); margin-bottom: 16px; }
    .character-bio-empty h3 { font: 600 32px/1.2 'Rajdhani',sans-serif; margin: 0 0 12px; }
    .character-bio-empty p { color: var(--fh-muted); font-size: 15px; margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto; }

    /* RELATED STORIES GRID */
    .fandom-content-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 26px; }
    @media(max-width:1000px) { .fandom-content-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } }
    @media(max-width:700px) { .fandom-content-grid { grid-template-columns: minmax(0,1fr); gap: 22px; } }

    .character-related-empty { grid-column: 1 / -1; text-align: center; padding: 80px 24px; border: 1px dashed var(--fh-line); border-radius: 16px; background: radial-gradient(ellipse at 50% 0,var(--fandom-glow),transparent 65%); }
    .character-related-empty .fh-icon { width: 56px; height: 56px; color: var(--fh-accent); margin-bottom: 16px; }
    .character-related-empty h3 { font: 600 32px/1.2 'Rajdhani',sans-serif; margin: 0 0 12px; }
    .character-related-empty p { color: var(--fh-muted); font-size: 15px; margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto; }

    /* PAGINATION */
    .character-pagination { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-top: 32px; border-top: 1px solid var(--fh-line); padding-top: 22px; font-size: 12px; }
    .character-pagination a { display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; border: 1px solid var(--fh-line); border-radius: 6px; color: var(--fh-text); transition: border-color .25s, color .25s; }
    .character-pagination a:hover { border-color: var(--fandom-color); color: var(--fandom-color); }
    .character-pagination > span { color: var(--fh-muted); }

    /* RESPONSIVE */
    @media(max-width:1100px) {
        .character-layout { grid-template-columns: 1fr; gap: 40px; }
        .character-cover { position: static; max-height: none; border-radius: 16px; max-width: 480px; margin: 0 auto; }
        .character-prose { max-width: none; }
    }
    @media(max-width:700px) {
        .character-hero { min-height: 250px; border-radius: 0 0 24px 24px; }
        .character-hero__content { padding: 24px; }
        .character-name { font-size: clamp(32px,9vw,64px); }
        .character-meta { gap: 12px; }
        .character-layout { padding: 32px 20px 60px; gap: 32px; }
        .character-cover { border-radius: 12px; }
        .character-bio-content { padding: 24px; }
    }
    @media(prefers-reduced-motion:reduce) { .character-hero,.character-hero img,.character-action-btn,.character-related-view-all { transition: none; } .character-hero:hover,.character-action-btn:hover,.character-related-view-all:hover { transform: none; } }
</style>

@section('title', $character->name.' | Fan Hub Plus')
@section('content')
<article class="fandom-reading fandom-page--{{ $character->category?->slug ?? 'all' }} character-page">

    <!-- HERO SECTION -->
    <section class="character-hero" aria-labelledby="character-name">

        <div class="character-hero__content">
            <div class="character-badge">
                <x-site-icon name="person-badge" class="fh-icon" />
                <span>{{ $character->category?->name ?? 'Character' }} Profile</span>
            </div>

            <h1 id="character-name" class="character-name">
                {{ $character->name }}
                <span>{{ $character->category?->name ?? 'Fandom Universe' }}</span>
            </h1>

            <div class="character-meta">
                <div class="character-meta-item">
                    <x-site-icon name="tag" class="fh-icon" />
                    <span><span class="character-meta-label">Fandom</span><span class="character-meta-value">{{ $character->category?->name ?? 'Unknown' }}</span></span>
                </div>
                @if($stories->total() > 0)
                    <div class="character-meta-item">
                        <x-site-icon name="book" class="fh-icon" />
                        <span><span class="character-meta-label">Appearances</span><span class="character-meta-value">{{ $stories->total() }} {{ Str::plural('story', $stories->total()) }}</span></span>
                    </div>
                @endif
                <div class="character-meta-item">
                    <x-site-icon name="calendar" class="fh-icon" />
                    <span><span class="character-meta-label">Member Since</span><span class="character-meta-value">{{ $character->created_at?->format('M Y') ?? 'Unknown' }}</span></span>
                </div>
            </div>
        </div>
    </section>

    <!-- SPLIT LAYOUT: LEFT COVER / RIGHT CONTENT -->
    <div class="character-layout">

        <!-- LEFT: CHARACTER COVER ARTWORK -->
        <aside class="character-cover" aria-hidden="true">
            <img src="{{ $character->artwork_url }}" 
                 alt="{{ $character->name }}" 
                 width="380" 
                 height="532" 
                 loading="eager" 
                 decoding="async"
                 data-image-fallback="{{ asset(config('homepage.images.character')) }}">
        </aside>

        <!-- RIGHT: CONTENT AREA -->
        <div class="character-prose">

            <!-- BIO SECTION -->
            <section class="character-section" aria-labelledby="bio-title">
                <header class="character-section-header">
                    <div class="character-section-title-group">
                        <p class="character-section-eyebrow">ABOUT</p>
                        <h2 id="bio-title" class="character-section-title">About <em>{{ $character->name }}</em></h2>
                    </div>
                </header>

                <div class="character-bio-card">
                    <div class="character-bio-content">
                        @if($character->bio)
                            <div class="fandom-reading__body fandom-reading__body--rich">
                                {!! \App\Support\SafeArticleHtml::render($character->bio) !!}
                            </div>
                        @else
                            <div class="character-bio-empty">
                                <x-site-icon name="journal-text" class="fh-icon" />
                                <h3>No Biography Yet</h3>
                                <p>A detailed biography for {{ $character->name }} hasn't been added yet. Check back soon!</p>
                            </div>
                        @endif
                    </div>
                </div>

            </section>

            <!-- RELATED STORIES SECTION -->
            @if($stories->isNotEmpty())
            <section class="character-section" aria-labelledby="related-stories-title">
                <header class="character-section-header">
                    <div class="character-section-title-group">
                        <p class="character-section-eyebrow">APPEARS IN</p>
                        <h2 id="related-stories-title" class="character-section-title">Stories featuring <em>{{ $character->name }}</em></h2>
                    </div>
                    <a href="{{ route('public.section', ['section' => 'characters']) }}" class="character-section-view-all">
                        View All <x-site-icon name="arrow-right" class="fh-icon" />
                    </a>
                </header>

                <div class="fandom-content-grid">
                    @foreach($stories as $story)
                        <x-fandom-content-card :content="$story" />
                    @endforeach
                </div>

                @if($stories->hasPages())
                <nav class="character-pagination" aria-label="Related stories pages">
                    @if($stories->previousPageUrl())
                        <a href="{{ $stories->previousPageUrl() }}" rel="prev"><x-site-icon name="chevron-left" class="fh-icon" /> Previous</a>
                    @else
                        <span aria-hidden="true"><x-site-icon name="chevron-left" class="fh-icon" /> Previous</span>
                    @endif
                    <span>Page {{ $stories->currentPage() }} of {{ $stories->lastPage() }}</span>
                    @if($stories->nextPageUrl())
                        <a href="{{ $stories->nextPageUrl() }}" rel="next">Next <x-site-icon name="chevron-right" class="fh-icon" /></a>
                    @else
                        <span aria-hidden="true">Next <x-site-icon name="chevron-right" class="fh-icon" /></span>
                    @endif
                </nav>
                @endif
            </section>
            @else
            <section class="character-section" aria-labelledby="related-stories-title">
                <header class="character-section-header">
                    <div class="character-section-title-group">
                        <p class="character-section-eyebrow">APPEARS IN</p>
                        <h2 id="related-stories-title" class="character-section-title">Stories featuring <em>{{ $character->name }}</em></h2>
                    </div>
                </header>

                <div class="fandom-content-grid">
                    <div class="character-related-empty">
                        <x-site-icon name="book" class="fh-icon" />
                        <h3>No Stories Yet</h3>
                        <p>{{ $character->name }} hasn't been featured in any published stories yet. When they appear in new content, it will show up here automatically.</p>
                        <a href="{{ route('public.explore', ['category' => $character->category?->slug]) }}" class="character-section-view-all" style="margin-top: 16px; padding: 12px 24px;">
                            <x-site-icon name="compass" class="fh-icon" />
                            Explore {{ $character->category?->name ?? 'Fandom' }} Content
                        </a>
                    </div>
                </div>
            </section>
            @endif

        </div>
    </div>

    <x-member-interactions :item="$character" type="character" />
</article>
@endsection