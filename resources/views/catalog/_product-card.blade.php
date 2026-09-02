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

<article class="group flex h-full flex-col overflow-hidden rounded-card border border-line bg-surface-raised shadow-card transition duration-200 ease-standard hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-float">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-[7/5] overflow-hidden bg-tint-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" @endif
                alt="{{ $product->name }}"
                width="700"
                height="500"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition duration-500 ease-standard group-hover:scale-[1.03]"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-primary/30">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.25" class="size-16" aria-hidden="true">
                    <path d="M3 5h14v10H3z" /><path d="m5 5 2-2h6l2 2" /><circle cx="10" cy="10" r="2.5" />
                </svg>
            </div>
        @endif

        <div class="pointer-events-none absolute left-3 top-3 flex flex-wrap gap-1.5">
            @if ($product->is_featured)
                <span class="inline-flex items-center rounded-pill bg-primary px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white">Destaque</span>
            @endif
            @if ($product->status === \App\Enums\ProductStatus::Unavailable)
                <span class="inline-flex items-center rounded-pill bg-warning-surface px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-warning-text">Indisponível</span>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">
            {{ $brandName ? $brandName . ' · ' : '' }}{{ $categoryName }}
        </p>

        <h3 class="line-clamp-2 font-display text-base font-semibold leading-snug text-foreground">
            <a href="{{ route('products.show', $product->slug) }}" class="transition duration-200 ease-standard hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
                {{ $product->name }}
            </a>
        </h3>

        @if ($product->short_description)
            <p class="line-clamp-2 text-sm leading-5 text-muted">{{ $product->short_description }}</p>
        @endif

        <div class="mt-auto flex flex-col gap-3 pt-2">
            @if ($showPrice)
                <p class="flex items-baseline gap-1.5">
                    <span class="text-sm text-muted">R$</span>
                    <span class="font-display text-2xl font-bold leading-none tracking-tight text-foreground">{{ number_format((float) $effectivePrice, 2, ',', '.') }}</span>
                </p>
            @elseif ($onRequest)
                <p class="inline-flex w-fit items-center gap-1.5 rounded-pill bg-tint-primary px-3 py-1.5 text-sm font-bold text-primary">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4">
                        <circle cx="10" cy="10" r="7" /><path d="M10 7v3l2 2" stroke-linecap="round" />
                    </svg>
                    Sob consulta
                </p>
            @else
                <p class="text-xs text-muted">Preço sob consulta comercial.</p>
            @endif

            <div class="flex gap-2">
                <button
                    type="button"
                    data-open-inquiry
                    data-product-id="{{ $product->id }}"
                    data-product-slug="{{ $product->slug }}"
                    data-product-name="{{ $product->name }}"
                    class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-control bg-primary px-4 text-sm font-bold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                    aria-label="{{ $inquiryLabel }}: {{ $product->name }}"
                >
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4">
                        <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h11A1.5 1.5 0 0 1 17 5.5v7a1.5 1.5 0 0 1-1.5 1.5H8l-4 3v-3H4.5A1.5 1.5 0 0 1 3 12.5v-7Z" stroke-linejoin="round" />
                    </svg>
                    {{ $inquiryLabel }}
                </button>

                <a
                    href="{{ route('products.show', $product->slug) }}"
                    class="inline-flex size-11 shrink-0 items-center justify-center rounded-control border border-line bg-surface-raised text-primary transition duration-200 ease-standard hover:border-primary/40 hover:bg-tint-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                    aria-label="Ver detalhes de {{ $product->name }}"
                >
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4">
                        <path d="M4 10h12M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</article>
