@props(['variant' => 'neutral'])

@php
    $variantClasses = [
        'neutral' => 'bg-surface text-muted ring-line',
        'primary' => 'bg-primary/10 text-primary-strong ring-primary/30',
        'success' => 'bg-success/10 text-foreground ring-success/30',
        'warning' => 'bg-warning/10 text-warning ring-warning/30',
        'danger' => 'bg-danger/10 text-danger ring-danger/30',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $variantClasses[$variant] ?? $variantClasses['neutral']]) }}>
    {{ $slot }}
</span>
