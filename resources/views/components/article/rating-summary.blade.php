@props([
    'average' => null,
    'count' => 0,
    'distribution' => [],
])

@php
    $hasRatings = $count > 0 && $average !== null;
    $stars = $hasRatings ? (float) $average : 0;
    $totalDistribution = array_sum(array_map('intval', $distribution));
@endphp

<section aria-labelledby="rating-summary-title" class="overflow-hidden rounded-card border border-line bg-surface-raised shadow-card">
    <div class="bg-gradient-to-r from-primary/[0.06] to-secondary/[0.04] px-5 py-4">
        <h2 id="rating-summary-title" class="font-display text-xl font-bold text-foreground">Avaliações dos leitores</h2>
    </div>

    <div class="grid gap-6 p-5 sm:grid-cols-[12rem_1fr] sm:items-center">
        <div class="text-center sm:text-left">
            @if ($hasRatings)
                <p class="font-display text-5xl font-bold text-foreground">{{ number_format($stars, 1, ',', '.') }}</p>
                <div class="mt-2 flex justify-center text-2xl text-warning sm:justify-start" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= round($stars) ? '★' : '☆' }}</span>
                    @endfor
                </div>
                <p class="mt-2 text-sm text-muted">Baseado em {{ $count }} {{ $count === 1 ? 'avaliação' : 'avaliações' }}</p>
            @else
                <p class="font-display text-3xl font-bold text-foreground">Sem notas ainda</p>
                <p class="mt-2 text-sm text-muted">Seja o primeiro a avaliar este post!</p>
            @endif
        </div>

        @if ($hasRatings)
            <ol class="space-y-1.5" aria-label="Distribuição de notas">
                @foreach ($distribution as $score => $total)
                    @php
                        $percentage = $totalDistribution > 0 ? round(($total / $totalDistribution) * 100) : 0;
                    @endphp
                    <li class="flex items-center gap-2 text-xs text-muted">
                        <span class="w-6 text-right font-semibold">{{ $score }}★</span>
                        <span class="h-2 flex-1 overflow-hidden rounded-pill bg-line">
                            <span class="block h-full bg-warning" style="width: {{ $percentage }}%"></span>
                        </span>
                        <span class="w-12 text-right">{{ $total }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</section>
