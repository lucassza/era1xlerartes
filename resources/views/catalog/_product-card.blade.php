@php
    /** @var \App\Models\Product $product */
    use App\Enums\PriceDisplayMode;

    $cover = $product->getFirstMedia('cover');
    $coverUrl = $cover?->hasGeneratedConversion('thumb-600')
        ? $cover->getUrl('thumb-600')
        : $cover?->getUrl();
    $coverSrcset = $cover?->hasResponsiveImages('thumb-600') ? $cover->getSrcset('thumb-600') : null;
    $mode = $product->price_display_mode;
    $effectivePrice = $product->effectivePrice();
    $showPrice = $mode === PriceDisplayMode::Show && $effectivePrice !== null;
    $onRequest = $mode === PriceDisplayMode::OnRequest;
    $inquiryLabel = $product->inquiry_button_text ?: ($settings['inquiry_button_text'] ?? 'Consulte');
    $categoryName = $product->category?->name ?? '—';
    $brandName = $product->brand?->name;
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-card border border-line bg-surface-raised transition duration-200 ease-standard hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-card">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-square overflow-hidden bg-surface focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" @endif
                alt="{{ $product->name }}"
                width="600"
                height="600"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition duration-500 ease-standard group-hover:scale-[1.03]"
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
            @if ($onRequest)
                <x-ui.badge variant="warning">Sob consulta</x-ui.badge>
            @endif
            @if ($product->status === \App\Enums\ProductStatus::Unavailable)
                <x-ui.badge variant="warning">Indisponível</x-ui.badge>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <header class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">
                {{ $categoryName }}@if ($brandName) · {{ $brandName }}@endif
            </p>
            <h3 class="line-clamp-2 text-base font-semibold leading-snug text-foreground">
                <a href="{{ route('products.show', $product->slug) }}" class="transition duration-200 ease-standard hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
                    {{ $product->name }}
                </a>
            </h3>
        </header>

        @if ($product->short_description)
            <p class="line-clamp-2 text-sm leading-5 text-muted">{{ $product->short_description }}</p>
        @endif

        <div class="mt-auto space-y-3 pt-2">
            @if ($showPrice)
                <p class="text-xl font-bold leading-none text-foreground">
                    R$ {{ number_format((float) $effectivePrice, 2, ',', '.') }}
                </p>
            @elseif ($onRequest)
                <p class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4">
                        <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h11A1.5 1.5 0 0 1 17 5.5v7a1.5 1.5 0 0 1-1.5 1.5H8l-4 3v-3H4.5A1.5 1.5 0 0 1 3 12.5v-7Z" stroke-linejoin="round" />
                    </svg>
                    Sob consulta
                </p>
            @else
                <p class="text-xs text-muted">Preço sob consulta comercial.</p>
            @endif

            <div class="space-y-1.5">
                <button
                    type="button"
                    data-open-inquiry
                    data-product-id="{{ $product->id }}"
                    data-product-slug="{{ $product->slug }}"
                    data-product-name="{{ $product->name }}"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-control bg-primary px-4 text-sm font-semibold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                    aria-label="{{ $inquiryLabel }}: {{ $product->name }}"
                >
                    {{ $inquiryLabel }}
                </button>
                <a
                    href="{{ route('products.show', $product->slug) }}"
                    class="inline-flex min-h-9 w-full items-center justify-center gap-1 text-sm font-semibold text-muted transition duration-200 ease-standard hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                >
                    Ver detalhes
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                        <path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</article>