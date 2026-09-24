@props(['slug' => null, 'label'])
<span {{ $attributes->class('home-badge') }}>
    <x-site-icon :name="$slug === 'merchandise' ? 'bag' : config('fandoms.'.$slug.'.icon', 'compass')" />
    <span>{{ $label }}</span>
</span>
