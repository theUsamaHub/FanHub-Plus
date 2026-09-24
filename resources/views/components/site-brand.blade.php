<a {{ $attributes->merge(['href' => route('home'), 'aria-label' => 'Fan Hub Plus home'])->class('fh-brand') }}>
    <x-site-icon name="crown" class="fh-brand__crown" />
    <span>FAN<span class="fh-brand__accent">HUB+</span></span>
</a>
