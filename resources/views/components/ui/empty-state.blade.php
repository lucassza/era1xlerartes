@props([
    'title',
    'description' => null,
])

<section {{ $attributes->class(['rounded-card border border-dashed border-line bg-surface p-6 text-center sm:p-8']) }}>
    @if (! $slot->isEmpty())
        <div aria-hidden="true" class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-surface-raised text-muted ring-1 ring-line [&>svg]:size-6">
            {{ $slot }}
        </div>
    @endif

    <h2 class="font-display text-xl font-bold text-foreground">{{ $title }}</h2>

    @if ($description)
        <p class="mx-auto mt-2 max-w-prose text-sm leading-6 text-muted">{{ $description }}</p>
    @endif
</section>
