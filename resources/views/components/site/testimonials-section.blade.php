@props([
    'testimonials',
    'showForm' => true,
])

<section aria-labelledby="testimonials-section-title" class="overflow-hidden rounded-card border border-line bg-surface-raised shadow-card">
    <div class="bg-gradient-to-r from-primary/[0.06] to-secondary/[0.04] px-5 py-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="testimonials-section-title" class="font-display text-xl font-bold text-foreground">O que dizem nossos leitores</h2>
            <a href="{{ route('testimonials.index') }}" class="text-sm font-semibold text-primary underline-offset-4 hover:underline">
                Ver todos
            </a>
        </div>
    </div>

    <div class="grid gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($testimonials as $testimonial)
            <article class="rounded-control border border-line bg-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-full bg-primary/10 text-sm font-bold uppercase text-primary">
                        {{ mb_strtoupper(mb_substr($testimonial->name, 0, 1)) }}
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-foreground">{{ $testimonial->name }}</p>
                        @if ($testimonial->city)
                            <p class="text-xs text-muted">{{ $testimonial->city }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3 text-sm text-warning" aria-label="Nota {{ $testimonial->score }} de 5">
                    @for ($i = 1; $i <= 5; $i++)
                        <span aria-hidden="true">{{ $i <= $testimonial->score ? '★' : '☆' }}</span>
                    @endfor
                </div>
                <p class="mt-3 text-sm leading-6 text-foreground">
                    {{ \Illuminate\Support\Str::limit($testimonial->content, 220) }}
                </p>
            </article>
        @empty
            <p class="col-span-full text-sm text-muted">Ainda não há depoimentos publicados.</p>
        @endforelse
    </div>

    @if ($showForm)
        <div class="border-t border-line bg-surface p-5">
            <p class="text-sm text-muted">
                Quer compartilhar sua história?
                <a href="{{ route('testimonials.index') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Envie seu depoimento</a>.
            </p>
        </div>
    @endif
</section>
