@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'placeholder' => '', 'hint' => null])
<div class="fh-auth-field" @if($type === 'password') x-data="{ visible: false }" @endif>
<label for="{{ $name }}">{{ $label }}</label>
<div class="fh-auth-input-wrap">
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type === 'password') :type="visible ? 'text' : 'password'" @else value="{{ old($name) }}" @endif autocomplete="{{ $autocomplete }}" placeholder="{{ $placeholder }}" required aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" @if($errors->has($name) || $hint) aria-describedby="{{ $errors->has($name) ? $name.'-error' : '' }} {{ $hint ? $name.'-hint' : '' }}" @endif {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
@if($type === 'password')<button class="fh-auth-reveal" type="button" x-cloak @click="visible = !visible" :aria-label="(visible ? 'Hide ' : 'Show ') + '{{ strtolower($label) }}'" :aria-pressed="visible" aria-controls="{{ $name }}"><i class="bi" :class="visible ? 'bi-eye-slash' : 'bi-eye'" aria-hidden="true"></i></button>@endif
</div>
@if($hint)<p class="fh-auth-hint" id="{{ $name }}-hint">{{ $hint }}</p>@endif
<x-input-error :messages="$errors->get($name)" id="{{ $name }}-error" role="alert" />
</div>
