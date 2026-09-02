<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">

    @php
        $cover = $product->getFirstMedia('cover');
        $gallery = $product->getMedia('gallery');
        $images = collect([$cover])->filter()->merge($gallery);
        $primary = $images->first();
        $attributeGroups = $product->attributeValues->groupBy(fn ($v) => $v->attribute->group->name ?? 'Outros');
        $mode = $product->price_display_mode;
        $effectivePrice = $product->effectivePrice();
        $showPrice = $mode === \App\Enums\PriceDisplayMode::Show && $effectivePrice !== null;
        $onRequest = $mode === \App\Enums\PriceDisplayMode::OnRequest;
        $inquiryLabel = $product->inquiry_button_text ?: ($settings->inquiry_button_text ?: 'Consulte');
        $isUnavailable = $product->status === \App\Enums\ProductStatus::Unavailable;
    @endphp

    <div class="mx-auto max-w-7xl px-4 pb-32 pt-8 sm:px-6 lg:px-8 lg:pb-16">
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-muted">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-primary">Início</a></li>
                <li aria-hidden="true">
                    <svg viewBox="0 0 16 16" fill="currentColor" class="size-3 text-line"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </li>
                <li><a href="{{ route('products.index') }}" class="hover:text-primary">Produtos</a></li>
                @if ($product->category)
                    <li aria-hidden="true">
                        <svg viewBox="0 0 16 16" fill="currentColor" class="size-3 text-line"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </li>
                    <li><a href="{{ route('catalog-categories.show', $product->category->slug) }}" class="hover:text-primary">{{ $product->category->name }}</a></li>
                @endif
                <li aria-hidden="true">
                    <svg viewBox="0 0 16 16" fill="currentColor" class="size-3 text-line"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </li>
                <li class="font-semibold text-foreground">{{ $product->name }}</li>
            </ol>
        </nav>

        <div
            x-data='{
                active: @json($images->keys()->first() ?? null),
                openLightbox(index) {
                    this.active = index;
                    this.$dispatch("open-lightbox");
                }
            }'
            class="grid gap-8 lg:grid-cols-2 lg:gap-10"
        >
            <section aria-label="Galeria do produto" class="flex flex-col gap-3">
                <div class="relative overflow-hidden rounded-feature border border-line bg-tint-primary">
                    @if ($primary)
                        <button
                            type="button"
                            @click="openLightbox(active)"
                            class="block w-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                            aria-label="Ampliar imagem principal"
                        >
                            @foreach ($images as $idx => $media)
                                <img
                                    x-show="active === {{ $idx }}"
                                    src="{{ $media->getUrl() }}"
                                    alt="{{ $product->name }}"
                                    class="aspect-square w-full object-cover"
                                    @unless ($loop->first) style="display: none;" @endunless
                                />
                            @endforeach
                        </button>
                    @else
                        <div class="flex aspect-square w-full items-center justify-center text-primary/30">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1" class="size-24" aria-hidden="true">
                                <path d="M3 5h14v10H3z" /><path d="m5 5 2-2h6l2 2" /><circle cx="10" cy="10" r="2.5" />
                            </svg>
                        </div>
                    @endif

                    <div class="pointer-events-none absolute left-5 top-5 flex flex-wrap gap-2">
                        @if ($product->is_featured)
                            <span class="inline-flex items-center rounded-pill bg-primary px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white">Destaque</span>
                        @endif
                        @if ($isUnavailable)
                            <span class="inline-flex items-center rounded-pill bg-warning-surface px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-warning-text">Indisponível</span>
                        @endif
                    </div>
                </div>

                @if ($images->count() > 1)
                    <ul class="grid grid-cols-5 gap-3">
                        @foreach ($images as $idx => $media)
                            <li>
                                <button
                                    type="button"
                                    @click="active = {{ $idx }}"
                                    class="block w-full overflow-hidden rounded-card border-2 transition duration-200 ease-standard focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                                    :class="active === {{ $idx }} ? 'border-primary' : 'border-line hover:border-primary/40'"
                                    aria-label="Selecionar imagem {{ $idx + 1 }}"
                                >
                                    <img src="{{ $media->hasGeneratedConversion('thumb-300') ? $media->getUrl('thumb-300') : $media->getUrl() }}" alt="" class="aspect-square w-full object-cover" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Lightbox (Alpine) --}}
                <div
                    x-data="{ open: false }"
                    x-show="open"
                    x-on:open-lightbox.window="open = true"
                    x-on:keyup.escape.window="open = false"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                    x-cloak
                    style="display: none;"
                >
                    <button type="button" @click="open = false" class="absolute right-4 top-4 inline-flex size-11 items-center justify-center rounded-pill text-white" aria-label="Fechar">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><path d="M5 5l10 10M15 5L5 15" stroke-linecap="round"/></svg>
                    </button>
                    @foreach ($images as $idx => $media)
                        <img x-show="active === {{ $idx }}" src="{{ $media->getUrl() }}" alt="{{ $product->name }}" class="max-h-[90vh] max-w-full rounded-card" />
                    @endforeach
                </div>
            </section>

            <section class="flex flex-col gap-5 lg:sticky lg:top-24 lg:self-start">
                <header class="flex flex-col gap-2">
                    <p class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wider">
                        @if ($product->brand?->name)
                            <span class="text-secondary">{{ $product->brand->name }}</span>
                        @endif
                        @if ($product->brand?->name && $product->category?->name)
                            <span class="size-1 rounded-pill bg-line" aria-hidden="true"></span>
                        @endif
                        @if ($product->category?->name)
                            <span class="text-muted">{{ $product->category->name }}</span>
                        @endif
                    </p>

                    <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">{{ $product->name }}</h1>

                    @if ($product->sku || $product->internal_code)
                        <p class="text-sm text-muted">
                            @if ($product->sku) SKU <span class="font-semibold text-foreground">{{ $product->sku }}</span> @endif
                            @if ($product->internal_code) · Cód. <span class="font-semibold text-foreground">{{ $product->internal_code }}</span> @endif
                        </p>
                    @endif
                </header>

                @if ($product->short_description)
                    <p class="text-base leading-7 text-foreground sm:text-lg">{{ $product->short_description }}</p>
                @endif

                {{-- Bloco de preço e consulta --}}
                <div class="flex flex-col gap-5 rounded-feature border border-line bg-surface-raised p-6 shadow-float">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            @if ($showPrice)
                                <span class="text-sm font-semibold text-muted">Preço de referência</span>
                                <p class="flex items-baseline gap-2">
                                    <span class="text-lg text-muted">R$</span>
                                    <span class="font-display text-4xl font-bold leading-none tracking-tight text-foreground">{{ number_format((float) $effectivePrice, 2, ',', '.') }}</span>
                                </p>
                            @elseif ($onRequest)
                                <p class="font-display text-2xl font-bold text-primary">Sob consulta</p>
                            @else
                                <p class="text-base text-muted">Preço sob consulta comercial.</p>
                            @endif
                        </div>

                        <span @class([
                            'inline-flex items-center gap-2 rounded-pill px-3.5 py-2 text-sm font-bold',
                            'bg-success-surface text-success-text' => ! $isUnavailable,
                            'bg-warning-surface text-warning-text' => $isUnavailable,
                        ])>
                            <span @class([
                                'size-2 rounded-pill',
                                'bg-success' => ! $isUnavailable,
                                'bg-warning' => $isUnavailable,
                            ]) aria-hidden="true"></span>
                            {{ $product->availability->label() }}
                        </span>
                    </div>

                    <div class="hidden gap-3 lg:flex">
                        <button
                            type="button"
                            data-open-inquiry
                            data-product-id="{{ $product->id }}"
                            data-product-slug="{{ $product->slug }}"
                            data-product-name="{{ $product->name }}"
                            class="inline-flex min-h-14 flex-1 items-center justify-center gap-2.5 rounded-control bg-primary px-6 text-base font-bold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                        >
                            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="size-5">
                                <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h11A1.5 1.5 0 0 1 17 5.5v7a1.5 1.5 0 0 1-1.5 1.5H8l-4 3v-3H4.5A1.5 1.5 0 0 1 3 12.5v-7Z" stroke-linejoin="round" />
                            </svg>
                            {{ $inquiryLabel }}
                        </button>
                    </div>

                    <p class="flex items-start gap-2.5 text-sm leading-6 text-muted">
                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 size-4 shrink-0 text-primary">
                            <circle cx="10" cy="10" r="7" /><path d="M10 7v3l2 2" stroke-linecap="round" />
                        </svg>
                        Respondemos por WhatsApp ou e-mail com preço, prazo e formas de entrega.
                    </p>
                </div>

                @if ($product->description)
                    <details class="rounded-card border border-line bg-surface-raised p-5" open>
                        <summary class="flex min-h-11 cursor-pointer items-center font-display text-base font-semibold text-foreground">Descrição completa</summary>
                        <div class="prose prose-sm mt-3 max-w-none text-foreground">
                            {!! $product->description !!}
                        </div>
                    </details>
                @endif

                @if ($product->specifications)
                    <details class="rounded-card border border-line bg-surface-raised p-5">
                        <summary class="flex min-h-11 cursor-pointer items-center font-display text-base font-semibold text-foreground">Especificações</summary>
                        <div class="prose prose-sm mt-3 max-w-none text-foreground">
                            {!! $product->specifications !!}
                        </div>
                    </details>
                @endif

                @if ($product->features)
                    <details class="rounded-card border border-line bg-surface-raised p-5">
                        <summary class="flex min-h-11 cursor-pointer items-center font-display text-base font-semibold text-foreground">Características</summary>
                        <div class="prose prose-sm mt-3 max-w-none text-foreground">
                            {!! $product->features !!}
                        </div>
                    </details>
                @endif

                @if ($attributeGroups->count() > 0)
                    <section aria-labelledby="attributes-heading">
                        <h2 id="attributes-heading" class="mb-3 font-display text-base font-semibold text-foreground">Atributos técnicos</h2>
                        <dl class="grid gap-3 sm:grid-cols-2">
                            @foreach ($attributeGroups as $groupName => $values)
                                @foreach ($values as $value)
                                    <div class="flex flex-col gap-0.5 rounded-control border border-line bg-surface-raised px-4 py-3">
                                        <dt class="text-xs text-muted">{{ $value->attribute->name }}</dt>
                                        <dd class="text-sm font-bold text-foreground">{{ $value->value ?? '—' }}</dd>
                                    </div>
                                @endforeach
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($product->tags->count() > 0)
                    <section aria-labelledby="tags-heading">
                        <h2 id="tags-heading" class="mb-2 text-sm font-semibold text-foreground">Tags</h2>
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($product->tags as $tag)
                                <li><span class="inline-flex min-h-8 items-center rounded-pill border border-line bg-surface-raised px-3.5 text-xs font-semibold text-muted">{{ $tag->name }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @php
                    $attachments = $product->getMedia('attachments');
                @endphp
                @if ($attachments->count() > 0)
                    <section aria-labelledby="attachments-heading">
                        <h2 id="attachments-heading" class="mb-2 text-sm font-semibold text-foreground">Arquivos anexos</h2>
                        <ul class="flex flex-col gap-1.5">
                            @foreach ($attachments as $file)
                                <li>
                                    <a href="{{ $file->getUrl() }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary hover:underline">
                                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4"><path d="M10 3v10m0 0-3-3m3 3 3-3M4 17h12"/></svg>
                                        {{ $file->name }}{{ $file->extension ? '.'.$file->extension : '' }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </section>
        </div>

        @if ($related->count() > 0)
            <section aria-labelledby="related-heading" class="mt-16">
                <x-ui.section-heading
                    id="related-heading"
                    title="Combina com este produto"
                    description="Outros materiais da mesma linha."
                    :href="$product->category ? route('catalog-categories.show', $product->category->slug) : null"
                    action="Ver categoria"
                />
                <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                    @foreach ($related as $relatedProduct)
                        @include('catalog._product-card', ['product' => $relatedProduct, 'settings' => $settings])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- CTA fixo (somente mobile) --}}
        <div class="fixed inset-x-0 bottom-0 z-30 flex items-center gap-3 border-t border-line bg-surface-raised/95 p-3 shadow-float backdrop-blur lg:hidden">
            @if ($showPrice)
                <p class="flex shrink-0 flex-col leading-tight">
                    <span class="text-[11px] text-muted">a partir de</span>
                    <span class="font-display text-lg font-bold text-foreground">R$ {{ number_format((float) $effectivePrice, 2, ',', '.') }}</span>
                </p>
            @endif
            <button
                type="button"
                data-open-inquiry
                data-product-id="{{ $product->id }}"
                data-product-slug="{{ $product->slug }}"
                data-product-name="{{ $product->name }}"
                class="inline-flex min-h-13 flex-1 items-center justify-center gap-2 rounded-control bg-primary px-5 text-base font-bold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
            >
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="size-5">
                    <path d="M3 5.5A1.5 1.5 0 0 1 4.5 4h11A1.5 1.5 0 0 1 17 5.5v7a1.5 1.5 0 0 1-1.5 1.5H8l-4 3v-3H4.5A1.5 1.5 0 0 1 3 12.5v-7Z" stroke-linejoin="round" />
                </svg>
                {{ $inquiryLabel }}
            </button>
        </div>
    </div>
</x-layouts.app>
