@props(['eyebrow', 'title', 'accent', 'subtitle', 'id'])
<header class="home-section-heading" data-reveal="up">
    <p class="home-eyebrow"><span></span>{{ $eyebrow }}<span></span></p>
    <h2 id="{{ $id }}">{{ $title }} <span>{{ $accent }}</span></h2>
    <p class="home-section-subtitle">{{ $subtitle }}</p>
</header>
