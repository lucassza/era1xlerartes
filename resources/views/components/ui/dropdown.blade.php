@props([
    'id',
    'label',
])

<div
    x-data="{
        open: false,
        toggle() { this.open ? this.close(false) : (this.open = true) },
        close(restoreFocus = false) {
            if (! this.open) return
            this.open = false
            if (restoreFocus) this.$nextTick(() => this.$refs.trigger.focus())
        },
    }"
    class="relative inline-block"
>
    <button
        x-ref="trigger"
        type="button"
        aria-haspopup="true"
        aria-controls="{{ $id }}-panel"
        :aria-expanded="open"
        @click="toggle()"
        {{ $attributes->except(['aria-controls', 'aria-expanded', 'aria-haspopup'])->class(['inline-flex min-h-11 items-center gap-2 rounded-control px-3 py-2 font-semibold text-foreground transition duration-200 ease-standard hover:bg-surface']) }}
    >
        <span>{{ $label }}</span>
        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" class="size-4">
            <path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div
        id="{{ $id }}-panel"
        x-cloak
        x-show="open"
        @click.outside="close(true)"
        @keyup.escape.window="if (open) close(true)"
        x-transition:enter="transition duration-200 ease-standard"
        x-transition:enter-start="origin-top scale-95 opacity-0"
        x-transition:enter-end="origin-top scale-100 opacity-100"
        x-transition:leave="transition duration-200 ease-standard"
        x-transition:leave-start="origin-top scale-100 opacity-100"
        x-transition:leave-end="origin-top scale-95 opacity-0"
        class="absolute right-0 z-20 mt-2 min-w-48 rounded-card border border-line bg-surface-raised p-2 shadow-float"
    >
        {{ $slot }}
    </div>
</div>
