<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">


    @php
        $cover = $product->getFirstMedia('cover');
        $gallery = $product->getMedia('gallery');
        $images = collect([$cover])->filter()->merge($gallery);
        $primary = $images->first();
        $attributeGroups = $product->attributeValues->groupBy(fn ($v) => $v->attribute->group->name ?? 'Outros');
    @endphp

    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-muted">
            <ol class="flex flex-wrap items-center gap-1">
                <li><a href="{{ route('home') }}" class="hover:text-primary">Início</a></li>
                <li aria-hidden="true">›</li>
                <li><a href="{{ route('products.index') }}" class="hover:text-primary">Produtos</a></li>
                @if ($product->category)
                    <li aria-hidden="true">›</li>
                    <li><a href="{{ route('catalog-categories.show', $product->category->slug) }}" class="hover:text-primary">{{ $product->category->name }}</a></li>
                @endif
                <li aria-hidden="true">›</li>
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
            class="grid gap-8 lg:grid-cols-2"
        >
            <section aria-label="Galeria do produto" class="space-y-4">
                <div class="overflow-hidden rounded-card border border-line bg-surface-raised">
                    @if ($primary)
                        <button
                            type="button"
                            @click="openLightbox({{ $images->keys()->first() }})"
                            class="block w-full"
                            aria-label="Ampliar imagem principal"
                        >
                            <img src="{{ $primary->getUrl() }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover" />
                        </button>
                    @else
                        <div class="flex aspect-square w-full items-center justify-center bg-surface text-muted">Sem imagem</div>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <ul class="grid grid-cols-5 gap-2">
                        @foreach ($images as $idx => $media)
                            <li>
                                <button
                                    type="button"
                                    @click="active = {{ $idx }}"
                                    @class([
                                        'block w-full overflow-hidden rounded-control border-2 transition',
                                        'border-primary' => $idx === 0,
                                        'border-line' => $idx !== 0,
                                    ])
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
                    <button type="button" @click="open = false" class="absolute right-4 top-4 text-white" aria-label="Fechar">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><path d="M5 5l10 10M15 5L5 15" stroke-linecap="round"/></svg>
                    </button>
                    @foreach ($images as $idx => $media)
                        <img x-show="active === {{ $idx }}" src="{{ $media->getUrl() }}" alt="{{ $product->name }}" class="max-h-[90vh] max-w-full rounded" />
                    @endforeach
                </div>
            </section>

            <section class="space-y-6">
                <header>
                    <p class="text-xs uppercase tracking-wider text-muted">
                        {{ $product->brand?->name ?? '' }}
                        {{ $product->brand?->name && $product->category?->name ? ' • ' : '' }}
                        {{ $product->category?->name ?? '' }}
                    </p>
                    <h1 class="mt-1 text-2xl font-bold text-foreground sm:text-3xl">{{ $product->name }}</h1>
                    @if ($product->sku || $product->internal_code)
                        <p class="mt-1 text-sm text-muted">
                            @if ($product->sku) SKU: <span class="font-semibold">{{ $product->sku }}</span> @endif
                            @if ($product->internal_code) · Cód.: <span class="font-semibold">{{ $product->internal_code }}</span> @endif
                        </p>
                    @endif
                </header>

                @php $mode = $product->price_display_mode; @endphp
                <div>
                    @if ($mode === \App\Enums\PriceDisplayMode::Show && $product->effectivePrice() !== null)
                        <p class="text-3xl font-bold text-foreground">R$ {{ number_format((float) $product->effectivePrice(), 2, ',', '.') }}</p>
                    @elseif ($mode === \App\Enums\PriceDisplayMode::OnRequest)
                        <p class="text-xl font-semibold text-primary">Sob consulta</p>
                    @else
                        <p class="text-sm text-muted">Preço sob consulta comercial.</p>
                    @endif
                    <p class="mt-1 text-xs text-muted">{{ $product->availability->label() }}</p>
                </div>

                @if ($product->short_description)
                    <p class="text-base text-foreground">{{ $product->short_description }}</p>
                @endif

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        data-open-inquiry
                        data-product-id="{{ $product->id }}"
                        data-product-slug="{{ $product->slug }}"
                        data-product-name="{{ $product->name }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-control bg-primary px-5 text-base font-semibold text-white shadow-sm hover:bg-primary-strong focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        {{ $product->inquiry_button_text ?: ($settings->inquiry_button_text ?: 'Consulte') }}
                    </button>
                </div>

                <details class="rounded-card border border-line bg-surface-raised p-4">
                    <summary class="cursor-pointer text-sm font-semibold text-foreground">Descrição completa</summary>
                    <div class="prose prose-sm mt-3 max-w-none text-foreground">
                        {!! $product->description !!}
                    </div>
                </details>

                @if ($product->specifications)
                    <details class="rounded-card border border-line bg-surface-raised p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-foreground">Especificações</summary>
                        <div class="prose prose-sm mt-3 max-w-none text-foreground">
                            {!! $product->specifications !!}
                        </div>
                    </details>
                @endif

                @if ($product->features)
                    <details class="rounded-card border border-line bg-surface-raised p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-foreground">Características</summary>
                        <div class="prose prose-sm mt-3 max-w-none text-foreground">
                            {!! $product->features !!}
                        </div>
                    </details>
                @endif

                @if ($attributeGroups->count() > 0)
                    <section>
                        <h2 class="mb-2 text-base font-semibold text-foreground">Atributos</h2>
                        <dl class="divide-y divide-line rounded-card border border-line bg-surface-raised">
                            @foreach ($attributeGroups as $groupName => $values)
                                @foreach ($values as $value)
                                    <div class="flex items-center justify-between gap-2 px-4 py-2 text-sm">
                                        <dt class="text-muted">{{ $value->attribute->name }}</dt>
                                        <dd class="font-semibold text-foreground">{{ $value->value ?? '—' }}</dd>
                                    </div>
                                @endforeach
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($product->tags->count() > 0)
                    <section>
                        <h2 class="mb-2 text-sm font-semibold text-foreground">Tags</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach ($product->tags as $tag)
                                <li><span class="inline-flex items-center rounded-full bg-surface-raised px-3 py-1 text-xs font-semibold text-foreground ring-1 ring-line">{{ $tag->name }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @php
                    $attachments = $product->getMedia('attachments');
                @endphp
                @if ($attachments->count() > 0)
                    <section>
                        <h2 class="mb-2 text-sm font-semibold text-foreground">Arquivos anexos</h2>
                        <ul class="space-y-1.5">
                            @foreach ($attachments as $file)
                                <li>
                                    <a href="{{ $file->getUrl() }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-primary hover:underline">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4"><path d="M10 3v10m0 0-3-3m3 3 3-3M4 17h12"/></svg>
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
                <h2 id="related-heading" class="mb-4 text-xl font-semibold text-foreground">Produtos relacionados</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($related as $relatedProduct)
                        @include('catalog._product-card', ['product' => $relatedProduct, 'settings' => $settings])
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</x-layouts.app>
