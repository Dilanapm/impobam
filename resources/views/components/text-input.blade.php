@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-border-strong bg-surface text-foreground focus:border-primary focus:ring-primary rounded-md shadow-sm']) }}>

