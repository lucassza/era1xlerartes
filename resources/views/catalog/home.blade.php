<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">
    @php
        $heroEnabled = (bool) $settings->getAttribute('hero_enabled');
        $heroHasContent = filled($settings->getAttribute('hero_title'))
            || filled($settings->getAttribute('hero_subtitle'))
            || (filled($settings->getAttribute('hero_cta_label')) && filled($settings->getAttribute('hero_cta_url')));
        $showHero = $heroEnabled && $heroHasContent;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        @if ($showHero)
            <x-site.catalog-hero :settings="$settings" />
        @else
            <header class="mb-8 max-w-3xl">
                <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">{{ $settings->title }}</h1>
                @if ($settings->intro_text)
                    <p class="mt-2 text-base leading-7 text-muted">{{ $settings->intro_text }}</p>
                @endif
            </header>

            <section aria-labelledby="search-heading">
                <h2 id="search-heading" class="sr-only">Buscar produtos</h2>
                <form method="GET" action="{{ route('products.index') }}" role="search" class="mx-auto max-w-2xl">
                    <label for="catalog-search" class="sr-only">Buscar produtos</label>
                    <div class="relative flex items-center">
                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75"
                            class="pointer-events-none absolute left-4 size-5 text-muted">
                            <circle cx="9" cy="9" r="5.5" /><path d="m14 14 3 3" stroke-linecap="round" />
                        </svg>
                        <input
                            id="catalog-search"
                            type="search"
                            name="buscar"
                            placeholder="O que você procura hoje?"
                            class="h-12 w-full rounded-pill border border-line bg-surface-raised pl-11 pr-28 text-sm text-foreground shadow-card placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-secondary sm:h-14 sm:text-base"
                        >
                        <button type="submit" class="absolute right-1.5 inline-flex h-9 items-center justify-center gap-2 rounded-pill bg-primary px-5 text-sm font-bold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary sm:h-11">
                            Buscar
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <div class="mt-12 space-y-16 sm:mt-16 sm:space-y-20">
            @if ($categories->count() > 0)
                <section aria-labelledby="categories-heading">
                    <x-ui.section-heading
                        id="categories-heading"
                        title="Comece pela técnica"
                        description="Encontre produtos por linha, aplicação ou segmento."
                        href="{{ route('products.index') }}"
                        action="Ver todas"
                    />

                    <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                        @foreach ($categories as $category)
                            @php
                                $activeChildren = $category->children->where('is_active', true)->count();
                            @endphp
                            <li>
                                <a
                                    href="{{ route('catalog-categories.show', $category->slug) }}"
                                    class="group flex h-full flex-col gap-4 rounded-card border border-line bg-surface-raised p-5 shadow-card transition duration-200 ease-standard hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-float focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                                >
                                    <span @class([
                                        'flex size-12 items-center justify-center rounded-card',
                                        'bg-primary/5 text-primary' => $loop->even,
                                        'bg-secondary/10 text-secondary' => $loop->odd,
                                    ])>
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6">
                                            <path d="M9 3h6v5l3 3v10H6V11l3-3V3Z" stroke-linejoin="round" /><path d="M9 6h6" stroke-linecap="round" />
                                        </svg>
                                    </span>

                                    <span class="flex flex-col gap-1.5">
                                        <span class="font-display text-base font-semibold leading-snug text-foreground transition duration-200 ease-standard group-hover:text-primary">
                                            {{ $category->name }}
                                        </span>
                                        @if ($category->description)
                                            <span class="line-clamp-2 text-sm leading-5 text-muted">{{ $category->description }}</span>
                                        @endif
                                    </span>

                                    <span class="mt-auto text-xs font-bold text-secondary">
                                        @if ($activeChildren > 0)
                                            {{ $activeChildren }} {{ $activeChildren === 1 ? 'subcategoria' : 'subcategorias' }}
                                        @else
                                            Explorar
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($featured->count() > 0)
                <section aria-labelledby="featured-heading">
                    <x-ui.section-heading
                        id="featured-heading"
                        title="Destaques da loja"
                        description="Selecionados pela equipe."
                        href="{{ route('products.index', ['destaque' => 1]) }}"
                        action="Ver todos"
                    />

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                        @foreach ($featured->take(4) as $product)
                            @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Explica o modelo de consulta: o catálogo não tem carrinho nem checkout --}}
            <section aria-labelledby="how-it-works-heading" class="rounded-feature bg-surface p-6 sm:p-10">
                <div class="max-w-2xl">
                    <h2 id="how-it-works-heading" class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Como funciona a consulta
                    </h2>
                    <p class="mt-2 text-base leading-7 text-muted">
                        Aqui não há carrinho nem pagamento online: você escolhe os materiais, envia uma consulta e a nossa equipe responde com preço, prazo e disponibilidade.
                    </p>
                </div>

                <ol class="mt-8 grid gap-4 sm:grid-cols-3 sm:gap-5">
                    <li class="flex flex-col gap-3 rounded-card bg-surface-raised p-5">
                        <span class="flex size-10 items-center justify-center rounded-pill bg-primary text-sm font-bold text-white">1</span>
                        <h3 class="font-display text-base font-semibold text-foreground">Escolha os materiais</h3>
                        <p class="text-sm leading-6 text-muted">Navegue por categoria, marca ou faixa de preço e abra o produto que interessa.</p>
                    </li>
                    <li class="flex flex-col gap-3 rounded-card bg-surface-raised p-5">
                        <span class="flex size-10 items-center justify-center rounded-pill bg-primary text-sm font-bold text-white">2</span>
                        <h3 class="font-display text-base font-semibold text-foreground">Envie sua consulta</h3>
                        <p class="text-sm leading-6 text-muted">Diga a quantidade, as cores e o prazo que você precisa. Leva menos de um minuto.</p>
                    </li>
                    <li class="flex flex-col gap-3 rounded-card bg-surface-raised p-5">
                        <span class="flex size-10 items-center justify-center rounded-pill bg-primary text-sm font-bold text-white">3</span>
                        <h3 class="font-display text-base font-semibold text-foreground">Receba a resposta</h3>
                        <p class="text-sm leading-6 text-muted">Retornamos por WhatsApp ou e-mail com tudo o que você precisa para decidir.</p>
                    </li>
                </ol>
            </section>

            <section aria-labelledby="latest-heading">
                <x-ui.section-heading
                    id="latest-heading"
                    title="Chegaram agora"
                    description="Os últimos itens adicionados ao catálogo."
                    href="{{ route('products.index') }}"
                    action="Ver catálogo completo"
                />

                @if ($latest->count() > 0)
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                        @foreach ($latest->take(4) as $product)
                            @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state title="Nenhum produto disponível no momento." description="Volte em breve para conferir novidades." />
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
