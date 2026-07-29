@props([
    'name',
    'value' => 5,
    'label' => 'Sua nota',
    'error' => null,
])

@php
    $groupId = "rating-{$name}";
    $safeValue = max(1, min(5, (int) $value));
@endphp

<fieldset
    id="{{ $groupId }}"
    role="radiogroup"
    aria-label="{{ $label }}"
    @class(['space-y-2', 'rounded-control border border-danger/40 p-2' => $error])
    x-data='{ score: {{ $safeValue }} }'
>
    <legend class="text-sm font-semibold text-foreground">{{ $label }}</legend>

    <div class="flex items-center gap-2">
        @for ($i = 1; $i <= 5; $i++)
            <label
                class="inline-flex size-10 cursor-pointer items-center justify-center rounded-control text-2xl transition"
                :class="score >= {{ $i }} ? 'text-warning' : 'text-line'"
            >
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $i }}"
                    class="sr-only"
                    x-on:click="score = {{ $i }}"
                    @checked($safeValue === $i)
                    @required(true)
                >
                <span aria-hidden="true">★</span>
                <span class="sr-only">{{ $i }} {{ $i === 1 ? 'estrela' : 'estrelas' }}</span>
            </label>
        @endfor
    </div>

    @if ($error)
        <p class="text-sm font-medium text-danger" role="alert">{{ $error }}</p>
    @endif
</fieldset>
