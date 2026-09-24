@props([])
<div class="fh-adm-spirit" aria-hidden="true">
    <svg class="fh-adm-spirit-svg" viewBox="0 0 260 180" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="spiritAura" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#dc63ff" stop-opacity=".55"/>
                <stop offset="55%" stop-color="#a757ff" stop-opacity=".25"/>
                <stop offset="100%" stop-color="#44d9ff" stop-opacity=".45"/>
            </linearGradient>
            <linearGradient id="spiritScarf" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stop-color="#e24b4b"/>
                <stop offset="100%" stop-color="#ff6b9d"/>
            </linearGradient>
            <linearGradient id="spiritBlade" x1="0" y1="1" x2="1" y2="0">
                <stop offset="0%" stop-color="#44d9ff" stop-opacity="0"/>
                <stop offset="45%" stop-color="#dcf7ff"/>
                <stop offset="100%" stop-color="#44d9ff" stop-opacity="0"/>
            </linearGradient>
            <radialGradient id="spiritCore" cx="50%" cy="45%" r="50%">
                <stop offset="0%" stop-color="#f3e8ff" stop-opacity=".9"/>
                <stop offset="55%" stop-color="#dc63ff" stop-opacity=".35"/>
                <stop offset="100%" stop-color="#05050d" stop-opacity="0"/>
            </radialGradient>
        </defs>

        <!-- energy field -->
        <ellipse class="spirit-aura" cx="130" cy="96" rx="92" ry="70" fill="url(#spiritAura)"/>
        <ellipse class="spirit-core" cx="130" cy="88" rx="48" ry="48" fill="url(#spiritCore)"/>

        <!-- water / chakra ribbons -->
        <path class="spirit-ribbon spirit-ribbon--a" d="M30 130 C70 90, 90 150, 130 110 S200 70, 230 105" stroke="#44d9ff" stroke-width="2" stroke-linecap="round" opacity=".7"/>
        <path class="spirit-ribbon spirit-ribbon--b" d="M40 150 C85 120, 110 165, 150 130 S210 100, 235 135" stroke="#dc63ff" stroke-width="1.5" stroke-linecap="round" opacity=".55"/>
        <path class="spirit-ribbon spirit-ribbon--c" d="M55 115 C95 85, 120 130, 160 95 S215 75, 240 100" stroke="#b49cff" stroke-width="1" stroke-linecap="round" opacity=".4"/>

        <!-- blade slash -->
        <path class="spirit-slash" d="M48 128 L210 48" stroke="url(#spiritBlade)" stroke-width="3" stroke-linecap="round"/>
        <path class="spirit-slash spirit-slash--thin" d="M60 140 L220 60" stroke="url(#spiritBlade)" stroke-width="1.2" stroke-linecap="round"/>

        <!-- abstract guild fighter silhouette -->
        <g class="spirit-figure" transform="translate(118 52)">
            <!-- head -->
            <circle cx="12" cy="14" r="10" fill="#0c0d18" stroke="#dc63ff" stroke-width="1.2"/>
            <!-- blindfold / six-eyes strip -->
            <rect x="3" y="12" width="18" height="3.5" rx="1.5" fill="#05050d" stroke="#44d9ff" stroke-width=".8"/>
            <circle class="spirit-eye spirit-eye--l" cx="7.5" cy="13.7" r="1.1" fill="#44d9ff"/>
            <circle class="spirit-eye spirit-eye--r" cx="16.5" cy="13.7" r="1.1" fill="#dc63ff"/>
            <!-- torso -->
            <path d="M2 26 L22 26 L19 62 L5 62 Z" fill="#0c0d18" stroke="#a757ff" stroke-width="1.1"/>
            <!-- flowing scarf -->
            <path class="spirit-scarf" d="M2 28 C-8 36, -4 52, -14 64 C-2 58, 6 48, 4 38" stroke="url(#spiritScarf)" stroke-width="3" stroke-linecap="round" fill="none"/>
            <path class="spirit-scarf spirit-scarf--b" d="M22 28 C34 34, 38 48, 30 62 C36 50, 32 40, 24 36" stroke="url(#spiritScarf)" stroke-width="2.2" stroke-linecap="round" fill="none" opacity=".8"/>
            <!-- arm + blade handle -->
            <path d="M20 34 L34 44" stroke="#8b9bb4" stroke-width="2.4" stroke-linecap="round"/>
            <path class="spirit-blade-handle" d="M34 44 L40 38" stroke="#ffd23f" stroke-width="2" stroke-linecap="round"/>
        </g>

        <!-- ground glow line -->
        <path class="spirit-ground" d="M40 158 H220" stroke="#dc63ff" stroke-width="1" opacity=".35" stroke-linecap="round"/>
    </svg>
    <div class="fh-adm-spirit-caption">{{ __('Guild Spirit') }}</div>
</div>
