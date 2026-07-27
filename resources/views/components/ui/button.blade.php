@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $variantClasses = [
        'primary' => 'bg-primary text-white hover:bg-primary-strong',
        'secondary' => 'bg-surface-raised text-foreground ring-1 ring-inset ring-line hover:bg-surface',
        'danger' => 'bg-danger text-white hover:opacity-90',
        'ghost' => 'bg-transparent text-foreground hover:bg-surface',
    ];
    $sizeClasses = [
        'sm' => 'min-h-9 px-3 text-sm',
        'md' => 'min-h-11 px-4 text-sm',
        'lg' => 'min-h-12 px-5 text-base',
    ];
@endphp

<button
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 rounded-control font-semibold transition duration-200 ease-standard disabled:pointer-events-none disabled:opacity-50',
        $variantClasses[$variant] ?? $variantClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
    ])->merge(['type' => $type]) }}
>
    {{ $slot }}
</button>
