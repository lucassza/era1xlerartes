@php
    /** @var \App\Models\Product $product */
    use App\Enums\PriceDisplayMode;

    $cover = $product->getFirstMedia('cover');
    $coverUrl = $cover?->hasGeneratedConversion('thumb-600')
        ? $cover->getUrl('thumb-600')
        : $cover?->getUrl();
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-card border border-line bg-surface-raised transition hover:border-primary/40 hover:shadow-card">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-square overflow-hidden bg-surface">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="{{ $product->name }}"
                width="600"
                height="600"
                loading="lazy"
                class="h-full w-full object-cover transition duration-300 ease-standard group-hover:scale-105"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-muted">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.25" class="size-12" aria-hidden="true">
                    <path d="M3 5h14v10H3z" /><path d="m5 5 2-2h6l2 2" /><circle cx="10" cy="10" r="2.5" />
                </svg>
            </div>
        @endif

        <div class="pointer-events-none absolute left-3 top-3 flex flex-wrap gap-1.5">
            @if ($product->is_featured)
                <x-ui.badge variant="primary">Destaque</x-ui.badge>
            @endif
            @if ($product->status === \App\Enums\ProductStatus::Unavailable)
                <x-ui.badge variant="warning">Indisponível</x-ui.badge>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <header class="space-y-1">
            <p class="text-xs uppercase tracking-wider text-muted">{{ $product->category?->name ?? '—' }}</p>
            <h3 class="line-clamp-2 text-base font-semibold text-foreground">
                <a href="{{ route('products.show', $product->slug) }}" class="hover:text-primary">
                    {{ $product->name }}
                </a>
            </h3>
        </header>

        @if ($product->short_description)
            <p class="line-clamp-2 text-sm text-muted">{{ $product->short_description }}</p>
        @endif

        <div class="mt-auto space-y-3">
            @php $mode = $product->price_display_mode; @endphp
            @if ($mode === PriceDisplayMode::Show && $product->effectivePrice() !== null)
                <p class="text-base font-bold text-foreground">
                    R$ {{ number_format((float) $product->effectivePrice(), 2, ',', '.') }}
                </p>
            @elseif ($mode === PriceDisplayMode::OnRequest)
                <p class="text-sm font-semibold text-primary">Sob consulta</p>
            @endif

            <div class="flex items-center gap-2">
                <a href="{{ route('products.show', $product->slug) }}" class="flex-1">
                    <x-ui.button variant="secondary" size="sm">Ver detalhes</x-ui.button>
                </a>
                <button
                    type="button"
                    data-open-inquiry
                    data-product-id="{{ $product->id }}"
                    data-product-slug="{{ $product->slug }}"
                    data-product-name="{{ $product->name }}"
                    class="inline-flex min-h-9 items-center justify-center gap-2 rounded-control bg-primary px-3 text-sm font-semibold text-white transition hover:bg-primary-strong"
                >
                    {{ $product->inquiry_button_text ?: ($settings['inquiry_button_text'] ?? 'Consulte') }}
                </button>
            </div>
        </div>
    </div>
</article>
