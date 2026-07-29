<x-layouts.app :meta="$meta" :json-ld="$jsonLd ?? []">
    @php
        $heroEnabled = (bool) $settings->getAttribute('hero_enabled');
        $heroHasContent = filled($settings->getAttribute('hero_title'))
            || filled($settings->getAttribute('hero_subtitle'))
            || (filled($settings->getAttribute('hero_cta_label')) && filled($settings->getAttribute('hero_cta_url')));
    @endphp

    <main id="main" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        @if ($heroEnabled && $heroHasContent)
            <x-site.catalog-hero :settings="$settings" />
        @endif

        @if (! ($heroEnabled && $heroHasContent))
            <header class="mb-8 max-w-3xl">
                <h1 class="font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">{{ $settings->title }}</h1>
                @if ($settings->intro_text)
                    <p class="mt-2 text-base leading-7 text-muted">{{ $settings->intro_text }}</p>
                @endif
            </header>
        @endif

        <div class="mt-8 space-y-16 sm:space-y-20">
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
                            class="h-12 w-full rounded-full border border-line bg-surface-raised pl-11 pr-28 text-sm text-foreground shadow-sm placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-secondary sm:h-14 sm:text-base"
                        >
                        <button type="submit" class="absolute right-1.5 inline-flex h-9 items-center justify-center gap-2 rounded-full bg-primary px-5 text-sm font-semibold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary sm:h-11">
                            Buscar
                        </button>
                    </div>
                </form>
            </section>

            @if ($categories->count() > 0)
                <section aria-labelledby="categories-heading">
                    <x-ui.section-heading
                        id="categories-heading"
                        title="Explore por categoria"
                        description="Encontre produtos por linha, aplicação ou segmento."
                    />

                    <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($categories as $category)
                            @php
                                $activeChildren = $category->children->where('is_active', true)->count();
                            @endphp
                            <li>
                                <a
                                    href="{{ route('catalog-categories.show', $category->slug) }}"
                                    class="group flex h-full flex-col justify-between gap-3 rounded-card border border-line bg-surface-raised p-5 transition duration-200 ease-standard hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-card focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
                                >
                                    <span class="font-display text-base font-semibold text-foreground transition duration-200 ease-standard group-hover:text-primary">
                                        {{ $category->name }}
                                    </span>
                                    <span class="flex items-center justify-between gap-2 text-sm text-muted">
                                        @if ($activeChildren > 0)
                                            <span>{{ $activeChildren }} {{ $activeChildren === 1 ? 'linha' : 'linhas' }}</span>
                                        @else
                                            <span>Explorar</span>
                                        @endif
                                        <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4 transition duration-200 ease-standard group-hover:translate-x-0.5 group-hover:text-primary">
                                            <path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
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
                        label="Em destaque"
                        title="Escolhidos a dedo para você"
                        href="{{ route('products.index', ['destaque' => 1]) }}"
                        action="Ver todos"
                    />

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                        @foreach ($featured as $product)
                            @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                        @endforeach
                    </div>
                </section>
            @endif

            <section aria-labelledby="latest-heading">
                <x-ui.section-heading
                    id="latest-heading"
                    label="Novidades"
                    title="Últimos produtos"
                    href="{{ route('products.index') }}"
                    action="Ver catálogo completo"
                />

                @if ($latest->count() > 0)
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                        @foreach ($latest as $product)
                            @include('catalog._product-card', ['product' => $product, 'settings' => $settings])
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state title="Nenhum produto disponível no momento." description="Volte em breve para conferir novidades." />
                @endif
            </section>
        </div>
    </main>
</x-layouts.app>