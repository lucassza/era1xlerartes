<x-layouts.app :meta="$meta">
    <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <section aria-labelledby="newsletter-unsubscribed-title" class="w-full rounded-card border border-line bg-surface-raised p-6 shadow-card sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">Newsletter</p>
            <h1 id="newsletter-unsubscribed-title" class="mt-2 font-display text-3xl font-bold tracking-tight text-foreground">Inscrição cancelada</h1>
            <x-ui.alert variant="info" class="mt-6" title="Preferências atualizadas">
                Você não receberá mais mensagens da newsletter.
            </x-ui.alert>
        </section>
    </div>
</x-layouts.app>
