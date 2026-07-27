<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">


    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-6">
            <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
                @if (! empty($activeFilters['buscar']))
                    Resultados para "{{ $activeFilters['buscar'] }}"
                @else
                    Catálogo de produtos
                @endif
            </h1>
            <p class="mt-1 text-sm text-muted">{{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }} encontrados</p>
        </header>

        <div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
            {{-- Filtros --}}
            <aside aria-label="Filtros do catálogo" class="lg:sticky lg:top-20 lg:self-start">
                <form method="GET" action="{{ route('products.index') }}" class="space-y-5 rounded-card border border-line bg-surface-raised p-4">
                    @if (! empty($activeFilters['buscar']))
                        <input type="hidden" name="buscar" value="{{ $activeFilters['buscar'] }}">
                    @endif
                    <input type="hidden" name="ordenar" value="{{ $sort }}">

                    <div>
                        <h2 class="mb-2 text-sm font-semibold text-foreground">Categoria</h2>
                        <select name="categoria" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm">
                            <option value="">Todas</option>
                            @foreach ($filters['categories'] ?? [] as $cat)
                                <option value="{{ $cat->slug }}" @selected(($activeFilters['categoria'] ?? '') === $cat->slug)>
                                    {{ $cat->name }}{{ $cat->parent_id ? '' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if (($filters['brands'] ?? collect())->count() > 0)
                        <div>
                            <h2 class="mb-2 text-sm font-semibold text-foreground">Marca</h2>
                            <select name="marca" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm">
                                <option value="">Todas</option>
                                @foreach ($filters['brands'] as $brand)
                                    <option value="{{ $brand->slug }}" @selected(($activeFilters['marca'] ?? '') === $brand->slug)>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <h2 class="mb-2 text-sm font-semibold text-foreground">Disponibilidade</h2>
                        <select name="disponibilidade" class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm">
                            <option value="">Todas</option>
                            @foreach (\App\Enums\ProductAvailability::cases() as $availability)
                                <option value="{{ $availability->value }}" @selected(($activeFilters['disponibilidade'] ?? '') === $availability->value)>
                                    {{ $availability->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if (! empty($filters['show_price_filter']))
                        <div>
                            <h2 class="mb-2 text-sm font-semibold text-foreground">Faixa de preço</h2>
                            <div class="flex items-center gap-2">
                                <input type="number" name="preco_min" step="0.01" min="0" value="{{ $activeFilters['preco_min'] ?? '' }}" placeholder="Mín." class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm" />
                                <span class="text-muted">–</span>
                                <input type="number" name="preco_max" step="0.01" min="0" value="{{ $activeFilters['preco_max'] ?? '' }}" placeholder="Máx." class="h-10 w-full rounded-control border border-line bg-surface px-3 text-sm" />
                            </div>
                        </div>
                    @endif

                    <label class="flex items-center gap-2 text-sm text-foreground">
                        <input type="checkbox" name="destaque" value="1" @checked(! empty($activeFilters['destaque'])) class="size-4 rounded border-line text-primary focus:ring-accent" />
                        Apenas destaques
                    </label>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-strong">Filtrar</button>
                        <a href="{{ route('products.index') }}" class="inline-flex h-10 items-center justify-center rounded-control border border-line bg-surface px-4 text-sm font-semibold text-foreground hover:bg-surface-raised">Limpar</a>
                    </div>
                </form>
            </aside>

            <section>
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm text-muted">Ordenar por</div>
                    <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
                        @foreach ($activeFilters as $key => $value)
                            @if (! is_null($value) && $key !== 'ordenar' && $key !== 'buscar' && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        @if (! empty($activeFilters['buscar']))
                            <input type="hidden" name="buscar" value="{{ $activeFilters['buscar'] }}">
                        @endif
                        <select name="ordenar" onchange="this.form.submit()" class="h-10 rounded-control border border-line bg-surface-raised px-3 text-sm">
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
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
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
                        <a href="{{ route('products.index') }}" class="mt-4 inline-flex h-10 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-strong">
                            Limpar filtros
                        </a>
                    </x-ui.empty-state>
                @endif
            </section>
        </div>
    </main>
</x-layouts.app>
