@props([])
<div class="fh-adm-spirit" aria-hidden="true">
    <svg class="fh-adm-spirit-svg" viewBox="0 0 240 96" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="spiritWave" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stop-color="#44d9ff" stop-opacity="0"/>
                <stop offset="35%" stop-color="#dc63ff" stop-opacity=".9"/>
                <stop offset="65%" stop-color="#44d9ff" stop-opacity=".9"/>
                <stop offset="100%" stop-color="#dc63ff" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="spiritSlash" x1="0" y1="1" x2="1" y2="0">
                <stop offset="0%" stop-color="#44d9ff" stop-opacity="0"/>
                <stop offset="50%" stop-color="#ffffff"/>
                <stop offset="100%" stop-color="#dc63ff" stop-opacity="0"/>
            </linearGradient>
            <radialGradient id="spiritOrb" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#ffffff"/>
                <stop offset="35%" stop-color="#dc63ff"/>
                <stop offset="100%" stop-color="#0a0a14" stop-opacity="0"/>
            </radialGradient>
        </defs>

        <!-- soft field -->
        <ellipse cx="120" cy="52" rx="100" ry="34" fill="url(#spiritOrb)" opacity=".18"/>

        <!-- energy waves -->
        <path class="spirit-wave spirit-wave--a" d="M10 58 C50 30, 70 78, 120 50 S200 22, 230 54" stroke="url(#spiritWave)" stroke-width="1.8" stroke-linecap="round"/>
        <path class="spirit-wave spirit-wave--b" d="M18 68 C60 44, 85 82, 125 60 S195 38, 228 66" stroke="url(#spiritWave)" stroke-width="1.2" stroke-linecap="round" opacity=".65"/>

        <!-- slash -->
        <path class="spirit-slash" d="M36 72 L204 28" stroke="url(#spiritSlash)" stroke-width="2.2" stroke-linecap="round"/>

        <!-- core orb (six-eyes / curse energy) -->
        <circle class="spirit-orb" cx="120" cy="50" r="14" fill="url(#spiritOrb)"/>
        <circle class="spirit-ring spirit-ring--a" cx="120" cy="50" r="14" stroke="#dc63ff" stroke-width="1" opacity=".7"/>
        <circle class="spirit-ring spirit-ring--b" cx="120" cy="50" r="14" stroke="#44d9ff" stroke-width=".8" opacity=".5"/>

        <!-- twin glints -->
        <circle class="spirit-glint" cx="113" cy="48" r="2.2" fill="#44d9ff"/>
        <circle class="spirit-glint spirit-glint--b" cx="127" cy="48" r="2.2" fill="#dc63ff"/>

        <!-- micro crown -->
        <g class="spirit-crown" transform="translate(110 14)">
            <path d="M0 8 L5 2 L10 8 L15 0 L20 8 L18 12 H2 Z" stroke="#cc46f7" stroke-width="1.1" fill="none" stroke-linejoin="round"/>
            <path d="M2 12 H18" stroke="#219eff" stroke-width="1.1" stroke-linecap="round"/>
        </g>
    </svg>
</div>
