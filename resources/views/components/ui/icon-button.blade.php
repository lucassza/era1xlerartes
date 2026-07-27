@props([
    'label',
    'size' => 'md',
    'type' => 'button',
])

@php
    $sizeClasses = [
        'sm' => 'size-9',
        'md' => 'size-11',
        'lg' => 'size-12',
    ];
@endphp

<button
    aria-label="{{ $label }}"
    {{ $attributes->except('aria-label')->class([
        'inline-flex shrink-0 items-center justify-center rounded-control bg-surface-raised text-foreground ring-1 ring-inset ring-line transition duration-200 ease-standard hover:bg-surface disabled:pointer-events-none disabled:opacity-50',
        $sizeClasses[$size] ?? $sizeClasses['md'],
    ])->merge(['type' => $type]) }}
>
    <span aria-hidden="true" class="inline-flex size-5 items-center justify-center [&>svg]:size-full">
        {{ $slot }}
    </span>
</button>
