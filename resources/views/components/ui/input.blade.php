@props([
    'id',
    'label',
    'type' => 'text',
    'help' => null,
    'error' => null,
    'labelSrOnly' => false,
])

@php
    $describedBy = implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        $help ? "{$id}-help" : null,
        $error ? "{$id}-error" : null,
    ]));
@endphp

<div class="space-y-2">
    <label for="{{ $id }}" @class(['block text-sm font-semibold text-foreground', 'sr-only' => $labelSrOnly])>
        {{ $label }}
    </label>

    <input
        {{ $attributes->except(['aria-describedby', 'aria-invalid'])->class([
            'block min-h-11 w-full rounded-control border bg-surface-raised px-3 py-2 text-foreground placeholder:text-muted transition duration-200 ease-standard',
            'border-line focus:border-accent' => ! $error,
            'border-danger focus:border-danger' => $error,
        ])->merge([
            'id' => $id,
            'type' => $type,
            'aria-describedby' => $describedBy ?: null,
            'aria-invalid' => $error ? 'true' : null,
        ]) }}
    >

    @if ($help)
        <p id="{{ $id }}-help" class="text-sm text-muted">{{ $help }}</p>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="text-sm font-medium text-danger">{{ $error }}</p>
    @endif
</div>
