<x-layouts.app :meta="$meta">
    <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <section aria-labelledby="newsletter-confirmed-title" class="w-full rounded-card border border-line bg-surface-raised p-6 shadow-card sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">Newsletter</p>
            <h1 id="newsletter-confirmed-title" class="mt-2 font-display text-3xl font-bold tracking-tight text-foreground">Inscrição confirmada</h1>
            <x-ui.alert variant="success" class="mt-6" title="Tudo pronto">
                Sua inscrição na newsletter foi confirmada com sucesso.
            </x-ui.alert>
        </section>
    </div>
</x-layouts.app>
