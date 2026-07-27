@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $variantClasses = [
        'info' => 'border-accent/30 bg-accent/10 text-foreground',
        'success' => 'border-success/30 bg-success/10 text-foreground',
        'warning' => 'border-warning/30 bg-warning/10 text-foreground',
        'danger' => 'border-danger/30 bg-danger/10 text-foreground',
    ];
@endphp

<div role="alert" {{ $attributes->class(['rounded-card border p-4', $variantClasses[$variant] ?? $variantClasses['info']]) }}>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif

    <div @class(['text-sm leading-6', 'mt-1.5' => $title])>{{ $slot }}</div>
</div>
