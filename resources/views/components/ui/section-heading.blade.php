@props([
    'as' => 'h2',
    'id' => null,
    'label' => null,
    'title',
    'description' => null,
    'href' => null,
    'action' => null,
    'border' => false,
])

@php
    $sizeClass = match ($as) {
        'h1' => 'text-3xl sm:text-4xl',
        'h3' => 'text-2xl',
        default => 'text-3xl',
    };
@endphp

<div @class(['mb-5 flex items-end justify-between gap-4', 'border-b border-line pb-4' => $border])>
    <div @class(['border-l-4 border-primary pl-4' => $label])>
        @if ($label)
            <p class="text-xs font-bold uppercase tracking-widest text-primary">{{ $label }}</p>
        @endif

        <{{ $as }}
            @if ($id) id="{{ $id }}" @endif
            @class(['font-display font-bold tracking-tight text-foreground', 'mt-1' => $label, $sizeClass])
        >{{ $title }}</{{ $as }}>

        @if ($description)
            <p class="mt-2 max-w-2xl text-lg leading-7 text-muted">{{ $description }}</p>
        @endif
    </div>

    @if ($href)
        <a href="{{ $href }}" class="shrink-0 rounded-control px-3 py-2 text-sm font-semibold text-muted transition duration-200 ease-standard hover:bg-surface hover:text-foreground">
            {{ $action ?? 'Ver todos' }}
        </a>
    @endif
</div>
