@props([
    'id',
    'title',
    'trigger',
])

<div x-data {{ $attributes->class(['inline-block']) }}>
    <button
        x-ref="opener"
        type="button"
        @click="$refs.dialog.showModal()"
        class="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-4 py-2 text-sm font-semibold text-white transition duration-200 ease-standard hover:bg-primary-strong"
    >
        {{ $trigger }}
    </button>

    <dialog
        id="{{ $id }}"
        x-ref="dialog"
        aria-labelledby="{{ $id }}-title"
        @close="$refs.opener.focus()"
        class="m-auto w-[min(calc(100%_-_2rem),36rem)] rounded-feature border border-line bg-surface-raised p-0 text-foreground shadow-float backdrop:bg-foreground/40"
    >
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <h2 id="{{ $id }}-title" class="font-display text-2xl font-bold">{{ $title }}</h2>
                <button
                    type="button"
                    aria-label="Fechar diálogo"
                    autofocus
                    @click="$refs.dialog.close()"
                    class="inline-flex size-11 shrink-0 items-center justify-center rounded-control text-foreground transition duration-200 ease-standard hover:bg-surface"
                >
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" class="size-5">
                        <path d="m5 5 10 10M15 5 5 15" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="mt-5 text-sm leading-6 text-muted">{{ $slot }}</div>
        </div>
    </dialog>
</div>
