<x-layouts.app :meta="$meta ?? null">
    <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <header class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">Comunidade</p>
            <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Depoimentos</h1>
            <p class="mx-auto max-w-2xl text-base leading-7 text-muted">
                Veja o que nossa comunidade está dizendo e compartilhe sua própria experiência com o Era1x Ler Artes.
            </p>
        </header>

        @if ($testimonialsEnabled)
            <x-forms.testimonial-form />
        @else
            <p class="rounded-card border border-warning/30 bg-warning/10 px-4 py-3 text-center text-sm text-foreground">
                O envio de depoimentos está temporariamente desabilitado.
            </p>
        @endif

        <section aria-labelledby="approved-testimonials-title" class="space-y-4">
            <h2 id="approved-testimonials-title" class="font-display text-2xl font-bold text-foreground">Aprovados recentemente</h2>

            @if ($testimonials->isEmpty())
                <x-ui.empty-state title="Sem depoimentos publicados" description="Assim que o administrador aprovar depoimentos, eles aparecerão aqui." />
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <article class="rounded-card border border-line bg-surface-raised p-5 shadow-card">
                            <header class="flex items-center gap-3">
                                <span class="inline-flex size-12 items-center justify-center rounded-full bg-primary/10 text-base font-bold uppercase text-primary">
                                    {{ mb_strtoupper(mb_substr($testimonial->name, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-foreground">{{ $testimonial->name }}</p>
                                    @if ($testimonial->city)
                                        <p class="text-xs text-muted">{{ $testimonial->city }}</p>
                                    @endif
                                    @if ($testimonial->approved_at)
                                        <p class="text-xs text-muted">{{ $testimonial->approved_at->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </header>
                            <div class="mt-3 text-sm text-warning" aria-label="Nota {{ $testimonial->score }} de 5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span aria-hidden="true">{{ $i <= $testimonial->score ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <p class="mt-3 text-sm leading-6 text-foreground">{{ $testimonial->content }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $testimonials->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
