@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-foreground-muted']) }}>
    {{ $value ?? $slot }}
</label>
