<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">
    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @php $banner = $settings->banner; @endphp
        @if (is_array($banner) && filled($banner['title'] ?? null))
            <section class="mb-8 rounded-card border border-line bg-surface-raised p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="max-w-2xl">
                        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">{{ $banner['title'] }}</h1>
                        @if (filled($banner['subtitle'] ?? null))
                            <p class="mt-2 text-base text-muted">{{ $banner['subtitle'] }}</p>
                        @endif
                        @if (filled($banner['cta_url'] ?? null))
                            <a href="{{ $banner['cta_url'] }}" class="mt-4 inline-block rounded-control bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-strong">
                                {{ $banner['cta_label'] ?? 'Ver produtos' }}
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <header class="mb-8 max-w-3xl">
            <h1 class="text-3xl font-bold text-foreground sm:text-4xl">{{ $settings->title }}</h1>
            @if ($settings->intro_text)
                <p class="mt-2 text-base text-muted">{{ $settings->intro_text }}</p>
            @endif
        </header>

        @if ($categories->count() > 0)
            <section aria-labelledby="categories-heading" class="mb-10">
                <h2 id="categories-heading" class="mb-4 text-xl font-semibold text-foreground">Categorias</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach ($categories as $category)
                        <li>
                            <a
                                href="{{ route('catalog-categories.show', $category->slug) }}"
                                class="inline-flex items-center rounded-full border border-line bg-surface-raised px-4 py-2 text-sm font-semibold text-foreground transition hover:border-primary/50 hover:bg-primary/5 hover:text-primary"
                            >
                                {{ $category->name }}
                                @if ($category->children->where('is_active', true)->count() > 0)
                                    <span class="ml-1.5 text-xs text-muted">({{ $category->children->where('is_active', true)->count() }})</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="GET" action="{{ route('products.index') }}" class="mb-10 flex items-center gap-2" role="search">
            <label for="catalog-search" class="sr-only">Buscar produtos</label>
            <input
                id="catalog-search"
                type="search"
                name="buscar"
                placeholder="Buscar por nome, SKU, descrição, marca..."
                class="h-11 flex-1 rounded-control border border-line bg-surface-raised px-4 text-sm text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-accent"
            >
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-strong">
                Buscar
            </button>
        </form>

        @if ($featured->count() > 0)
            <section aria-labelledby="featured-heading" class="mb-12">
                <div class="mb-4 flex items-center justify-between">
                    <h2 id="featured-heading" class="text-xl font-semibold text-foreground">Produtos em destaque</h2>
                    <a href="{{ route('products.index', ['destaque' => 1]) }}" class="text-sm font-semibold text-primary hover:underline">Ver todos</a>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured as $product)
                        @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                    @endforeach
                </div>
            </section>
        @endif

        <section aria-labelledby="latest-heading" class="mb-12">
            <div class="mb-4 flex items-center justify-between">
                <h2 id="latest-heading" class="text-xl font-semibold text-foreground">Últimos produtos</h2>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-primary hover:underline">Ver catálogo completo</a>
            </div>
            @if ($latest->count() > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($latest as $product)
                        @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                    @endforeach
                </div>
            @else
                <x-ui.empty-state title="Nenhum produto disponível no momento." description="Volte em breve para conferir novidades." />
            @endif
        </section>
    </main>
</x-layouts.app>
