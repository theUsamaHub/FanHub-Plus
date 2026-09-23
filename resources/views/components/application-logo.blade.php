@props(['class' => ''])

    <span {{ $attributes->merge(['class' => 'fw-bold text-primary fs-4']) }} style="font-family: 'Rajdhani', sans-serif;">
    {{ config('app.name', 'FanHubPlus') }}
</span>
