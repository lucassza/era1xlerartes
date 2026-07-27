<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">


    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-muted">
            <ol class="flex flex-wrap items-center gap-1">
                <li><a href="{{ route('home') }}" class="hover:text-primary">Início</a></li>
                <li aria-hidden="true">›</li>
                <li><a href="{{ route('products.index') }}" class="hover:text-primary">Produtos</a></li>
                <li aria-hidden="true">›</li>
                <li class="font-semibold text-foreground">{{ $category->name }}</li>
            </ol>
        </nav>

        <header class="mb-6">
            <h1 class="text-2xl font-bold text-foreground sm:text-3xl">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-1 text-sm text-muted">{{ $category->description }}</p>
            @endif
        </header>

        @if ($subcategories->count() > 0)
            <nav aria-label="Subcategorias" class="mb-6">
                <ul class="flex flex-wrap gap-2">
                    @foreach ($subcategories as $sub)
                        <li>
                            <a href="{{ route('catalog-categories.show', $sub->slug) }}" class="inline-flex items-center rounded-full border border-line bg-surface-raised px-4 py-1.5 text-sm text-foreground hover:border-primary/50 hover:text-primary">
                                {{ $sub->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <p class="mb-4 text-sm text-muted">{{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }} encontrados</p>

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
                title="Nenhum produto encontrado nesta categoria."
                description="Volte para o catálogo ou explore outras categorias."
            >
                <a href="{{ route('products.index') }}" class="mt-4 inline-flex h-10 items-center justify-center rounded-control bg-primary px-4 text-sm font-semibold text-white hover:bg-primary-strong">
                    Ver catálogo
                </a>
            </x-ui.empty-state>
        @endif
    </main>
</x-layouts.app>
