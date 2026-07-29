<dialog
    x-data="{
        opener: null,
        open(event) {
            this.opener = event.detail?.opener ?? document.activeElement
            this.$refs.dialog.showModal()
            this.$nextTick(() => this.$refs.query.focus())
        },
        close() { this.$refs.dialog.close() },
    }"
    x-ref="dialog"
    x-cloak
    aria-labelledby="site-search-dialog-title"
    @open-search-dialog.window="open($event)"
    @cancel.prevent="close()"
    @close="opener?.focus()"
    class="m-auto w-[min(calc(100%_-_2rem),36rem)] rounded-feature border border-line bg-surface-raised p-0 text-foreground shadow-float backdrop:bg-foreground/40"
>
    <div class="p-6 sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <h2 id="site-search-dialog-title" class="font-display text-2xl font-bold">Buscar no blog</h2>
            <button type="button" aria-label="Fechar busca" @click="close()" class="inline-flex size-11 shrink-0 items-center justify-center rounded-control text-foreground transition duration-200 ease-standard hover:bg-surface">
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" class="size-5">
                    <path d="m5 5 10 10M15 5 5 15" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <form method="GET" action="{{ route('search.index') }}" class="mt-6 flex gap-3">
            <label for="site-search-dialog-query" class="sr-only">Termo de busca</label>
            <input x-ref="query" id="site-search-dialog-query" name="q" type="search" required placeholder="Buscar posts..." class="min-h-11 min-w-0 flex-1 rounded-control border border-line bg-surface px-3 py-2 text-foreground placeholder:text-muted">
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-4 py-2 text-sm font-semibold text-white transition duration-200 ease-standard hover:bg-primary-strong">Buscar</button>
        </form>
    </div>
</dialog>
