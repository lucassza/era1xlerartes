<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">

    @php
        $totalProducts = $products->total();
        $productWord = $totalProducts === 1 ? 'produto' : 'produtos';
        $headerTitle = ! empty($activeFilters['buscar'])
            ? 'Resultados para "' . $activeFilters['buscar'] . '"'
            : 'Catálogo de produtos';

        $filterKeys = ['categoria', 'marca', 'disponibilidade', 'preco_min', 'preco_max', 'destaque'];
        $activeFilterCount = count(array_filter($filterKeys, fn (string $key) => ! empty($activeFilters[$key])));

        $chips = collect();
        if (! empty($activeFilters['categoria'])) {
            $cat = collect($filters['categories'] ?? [])->firstWhere('slug', $activeFilters['categoria']);
            $chips->push(['label' => 'Categoria: ' . ($cat?->name ?? $activeFilters['categoria']), 'remove' => ['categoria']]);
        }
        if (! empty($activeFilters['marca'])) {
            $brand = collect($filters['brands'] ?? [])->firstWhere('slug', $activeFilters['marca']);
            $chips->push(['label' => 'Marca: ' . ($brand?->name ?? $activeFilters['marca']), 'remove' => ['marca']]);
        }
        if (! empty($activeFilters['disponibilidade'])) {
            $availability = \App\Enums\ProductAvailability::tryFrom($activeFilters['disponibilidade']);
            $chips->push(['label' => ($availability?->label() ?? $activeFilters['disponibilidade']), 'remove' => ['disponibilidade']]);
        }
        if (! empty($activeFilters['preco_min']) || ! empty($activeFilters['preco_max'])) {
            $min = $activeFilters['preco_min'] ?? null;
            $max = $activeFilters['preco_max'] ?? null;
            $priceLabel = match (true) {
                $min !== null && $max !== null && $min !== '' && $max !== '' => 'R$ ' . $min . ' – R$ ' . $max,
                $min !== null && $min !== '' => 'A partir de R$ ' . $min,
                default => 'Até R$ ' . $max,
            };
            $chips->push(['label' => $priceLabel, 'remove' => ['preco_min', 'preco_max']]);
        }
        if (! empty($activeFilters['destaque'])) {
            $chips->push(['label' => 'Apenas destaques', 'remove' => ['destaque']]);
        }
    @endphp

    <div x-data="{ filtersOpen: false }" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="mb-4 flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('home') }}" class="hover:text-primary">Início</a>
            <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-3 text-line"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
            <span class="font-semibold text-foreground">Catálogo de produtos</span>
        </nav>

        <header class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0 max-w-2xl">
                <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">{{ $headerTitle }}</h1>
                <p class="mt-2 text-base leading-7 text-muted">
                    Materiais de arte e artesanato selecionados. Escolha o que precisa e envie uma consulta — respondemos com preço, prazo e disponibilidade.
                </p>
            </div>
            <p class="flex flex-col items-start gap-0.5 sm:items-end">
                <span class="font-display text-3xl font-bold leading-none text-primary">{{ $totalProducts }}</span>
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $productWord }} {{ $totalProducts === 1 ? 'encontrado' : 'encontrados' }}</span>
            </p>
        </header>

        {{-- Barra de ações do mobile: abre a folha de filtros e ordena --}}
        <div class="mb-4 flex items-center gap-2 lg:hidden">
            <button
                type="button"
                @click="filtersOpen = true"
                class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-control border border-primary/20 bg-primary/5 px-4 text-sm font-bold text-primary transition duration-200 ease-standard focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
            >
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" class="size-4">
                    <path d="M3 6h14M6 10h8M9 14h2" stroke-linecap="round" />
                </svg>
                Filtrar
                @if ($activeFilterCount > 0)
                    <span class="inline-flex min-w-5 items-center justify-center rounded-pill bg-primary px-1.5 text-[11px] font-bold text-white">{{ $activeFilterCount }}</span>
                @endif
            </button>

            <form method="GET" action="{{ route('products.index') }}" class="flex-1">
                @foreach ($activeFilters as $key => $value)
                    @if (! is_null($value) && $key !== 'ordenar' && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="sort-select-mobile" class="sr-only">Ordenar produtos</label>
                <select id="sort-select-mobile" name="ordenar" onchange="this.form.submit()" class="h-11 w-full rounded-control border border-line bg-surface-raised px-3 text-sm font-semibold text-foreground focus:outline-none focus:ring-2 focus:ring-secondary">
                    <option value="recent" @selected($sort === 'recent')>Mais recentes</option>
                    <option value="name_asc" @selected($sort === 'name_asc')>Nome A→Z</option>
                    <option value="name_desc" @selected($sort === 'name_desc')>Nome Z→A</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Menor preço</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Maior preço</option>
                    <option value="featured" @selected($sort === 'featured')>Destaques</option>
                    <option value="sort_order" @selected($sort === 'sort_order')>Ordem manual</option>
                </select>
            </form>
        </div>

        <div class="grid gap-8 lg:grid-cols-[18rem_minmax(0,1fr)]">
            {{-- Trilho de filtros: fixo no desktop --}}
            <aside aria-label="Filtros do catálogo" class="hidden lg:sticky lg:top-24 lg:block lg:self-start">
                <div class="rounded-card border border-line bg-surface-raised p-5">
                    <div class="mb-4 flex items-center justify-between border-b border-line pb-4">
                        <h2 class="inline-flex items-center gap-2 font-display text-base font-semibold text-foreground">
                            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4 text-primary">
                                <path d="M3 6h14M6 10h8M9 14h2" stroke-linecap="round" />
                            </svg>
                            Filtros
                        </h2>
                        @if ($activeFilterCount > 0)
                            <a href="{{ route('products.index') }}" class="text-sm font-bold text-secondary hover:text-secondary-strong">Limpar</a>
                        @endif
                    </div>

                    @include('catalog._filters', ['scope' => 'desktop'])
                </div>
            </aside>

            {{-- Folha inferior de filtros: mobile --}}
            <div
                x-cloak
                x-show="filtersOpen"
                @keyup.escape.window="filtersOpen = false"
                class="fixed inset-0 z-50 lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="Filtros do catálogo"
            >
                <div @click="filtersOpen = false" class="absolute inset-0 bg-foreground/50" aria-hidden="true"></div>

                <div
                    x-transition:enter="transition duration-200 ease-standard"
                    x-transition:enter-start="translate-y-full"
                    x-transition:enter-end="translate-y-0"
                    class="absolute inset-x-0 bottom-0 flex max-h-[92vh] flex-col rounded-t-feature bg-surface-raised shadow-float"
                >
                    <div class="flex flex-col gap-3 border-b border-line px-5 pb-4 pt-2.5">
                        <span class="mx-auto h-1.5 w-11 rounded-pill bg-line" aria-hidden="true"></span>
                        <div class="flex items-center justify-between">
                            <h2 class="font-display text-xl font-bold text-foreground">Filtros</h2>
                            <button
                                type="button"
                                @click="filtersOpen = false"
                                class="inline-flex size-11 items-center justify-center rounded-pill bg-surface text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                            >
                                <span class="sr-only">Fechar filtros</span>
                                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4"><path d="M5 5l10 10M15 5L5 15" stroke-linecap="round" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-y-auto px-5 py-5">
                        @include('catalog._filters', ['scope' => 'mobile'])
                    </div>
                </div>
            </div>

            <section>
                {{-- Barra de resultados: chips ativos + ordenação (desktop) --}}
                <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-card border border-line bg-surface-raised px-4 py-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($chips->isNotEmpty())
                            @foreach ($chips as $chip)
                                <a
                                    href="{{ route('products.index', request()->except([...$chip['remove'], 'page'])) }}"
                                    class="inline-flex min-h-9 items-center gap-2 rounded-pill border border-primary/20 bg-primary/5 px-3.5 text-xs font-bold text-primary transition duration-200 ease-standard hover:border-primary/60 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                                >
                                    {{ $chip['label'] }}
                                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-3">
                                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L8 6.94l2.72-2.72a.75.75 0 1 1 1.06 1.06L9.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L8 9.06l-2.72 2.72a.75.75 0 0 1-1.06-1.06L6.94 8 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="sr-only">(remover filtro)</span>
                                </a>
                            @endforeach
                        @else
                            <span class="text-sm text-muted">Nenhum filtro aplicado</span>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('products.index') }}" class="hidden shrink-0 items-center gap-2 lg:flex">
                        @foreach ($activeFilters as $key => $value)
                            @if (! is_null($value) && $key !== 'ordenar' && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="sort-select" class="text-sm text-muted">Ordenar por</label>
                        <select id="sort-select" name="ordenar" onchange="this.form.submit()" class="h-10 rounded-control border border-line bg-surface px-3 text-sm font-semibold text-foreground focus:outline-none focus:ring-2 focus:ring-secondary">
                            <option value="recent" @selected($sort === 'recent')>Mais recentes</option>
                            <option value="name_asc" @selected($sort === 'name_asc')>Nome A→Z</option>
                            <option value="name_desc" @selected($sort === 'name_desc')>Nome Z→A</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>Menor preço</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>Maior preço</option>
                            <option value="featured" @selected($sort === 'featured')>Destaques</option>
                            <option value="sort_order" @selected($sort === 'sort_order')>Ordem manual</option>
                        </select>
                    </form>
                </div>

                @if ($products->count() > 0)
                    <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-3">
                        @foreach ($products as $product)
                            @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @else
                    <x-ui.empty-state
                        title="Nenhum produto encontrado"
                        description="Tente remover filtros ou ajustar os termos da busca."
                    >
                        <x-slot:actions>
                            <a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-4 text-sm font-bold text-white hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
                                Limpar filtros
                            </a>
                            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-surface px-4 text-sm font-semibold text-foreground hover:bg-surface-raised focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
                                Explorar categorias
                            </a>
                        </x-slot:actions>
                    </x-ui.empty-state>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
