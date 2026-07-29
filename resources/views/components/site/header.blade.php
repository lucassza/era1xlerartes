@php
    $routeName = request()->route()?->getName() ?? '';
    $routeSlug = request()->route('slug');
    $catalogActive = $routeName === 'home' || str_starts_with($routeName, 'products.') || str_starts_with($routeName, 'catalog-categories.');
    $blogActive = $routeName === 'blog.index' || ($routeName && (str_starts_with($routeName, 'posts.') || str_starts_with($routeName, 'categories.') || str_starts_with($routeName, 'tags.') || str_starts_with($routeName, 'authors.')));
    $contactActive = $routeName === 'site.contact';
@endphp

<header
    x-data="{
        mobileOpen: false,
        closeMobile(restoreFocus = false) {
            if (! this.mobileOpen) return
            this.mobileOpen = false
            if (restoreFocus) this.$nextTick(() => this.$refs.menuTrigger.focus())
        },
    }"
    class="sticky top-0 z-40 border-b border-line bg-surface-raised"
>
    {{-- Main bar --}}
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="shrink-0 rounded-control" aria-label="{{ config('app.name', 'Era1x Ler Artes') }} — página inicial">
            <img src="{{ asset('images/brand/logo-era1x.png') }}" alt="{{ config('app.name', 'Era1x Ler Artes') }}" width="500" height="500"
                class="h-12 w-12 object-contain sm:h-14 sm:w-14">
        </a>

        {{-- Primary nav (desktop): Início, Produtos, Categorias, Blog, Contato --}}
        <nav aria-label="Navegação principal" class="hidden min-w-0 flex-1 md:block">
            <div class="flex items-center gap-0.5 overflow-x-clip">
                <a
                    href="{{ route('home') }}"
                    @class([
                        'shrink-0 rounded-control px-3 py-2 text-sm font-semibold transition duration-200 ease-standard hover:bg-surface',
                        'text-primary bg-primary/5' => $catalogActive && $routeName === 'home',
                        'text-muted hover:text-foreground' => ! ($catalogActive && $routeName === 'home'),
                    ])
                    @if ($catalogActive && $routeName === 'home') aria-current="page" @endif
                >
                    Início
                </a>

                <a
                    href="{{ route('products.index') }}"
                    @class([
                        'shrink-0 rounded-control px-3 py-2 text-sm font-semibold transition duration-200 ease-standard hover:bg-surface',
                        'text-primary bg-primary/5' => $catalogActive && str_starts_with($routeName, 'products.'),
                        'text-muted hover:text-foreground' => ! ($catalogActive && str_starts_with($routeName, 'products.')),
                    ])
                    @if ($catalogActive && str_starts_with($routeName, 'products.')) aria-current="page" @endif
                >
                    Produtos
                </a>

                @if (($navigationCatalogCategories ?? collect())->count() > 0)
                    <div
                        x-data="{ open: false }"
                        @mouseenter="open = true"
                        @mouseleave="open = false"
                        @focusin="open = true"
                        @focusout="open = false"
                        @click.outside="open = false"
                        class="relative shrink-0"
                    >
                        <button
                            type="button"
                            @click.stop="open = true"
                            @class([
                                'inline-flex items-center gap-1 rounded-control px-3 py-2 text-sm font-semibold transition duration-200 ease-standard hover:bg-surface',
                                'text-primary bg-primary/5' => str_starts_with($routeName, 'catalog-categories.'),
                                'text-muted hover:text-foreground' => ! str_starts_with($routeName, 'catalog-categories.'),
                            ])
                            :aria-expanded="open"
                            aria-haspopup="true"
                            aria-label="Abrir lista de categorias"
                        >
                            Categorias
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5" :class="{ 'rotate-180': open }"><path d="m5 7 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition duration-150 ease-standard"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            @class([
                                'absolute left-0 top-full z-50 mt-1 w-64 rounded-card border bg-surface-raised p-2 shadow-card',
                                'border-line' => true,
                            ])
                        >
                            <ul class="max-h-80 overflow-auto">
                                @foreach ($navigationCatalogCategories as $cat)
                                    <li>
                                        <a
                                            href="{{ route('catalog-categories.show', $cat->slug) }}"
                                            class="block rounded-control px-3 py-2 text-sm text-foreground hover:bg-surface hover:text-primary"
                                        >
                                            {{ $cat->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <a
                    href="{{ route('blog.index') }}"
                    @class([
                        'shrink-0 rounded-control px-3 py-2 text-sm font-semibold transition duration-200 ease-standard hover:bg-surface',
                        'text-primary bg-primary/5' => $blogActive,
                        'text-muted hover:text-foreground' => ! $blogActive,
                    ])
                    @if ($blogActive) aria-current="page" @endif
                >
                    Blog
                </a>

                <a
                    href="{{ route('site.contact') }}"
                    @class([
                        'shrink-0 rounded-control px-3 py-2 text-sm font-semibold transition duration-200 ease-standard hover:bg-surface',
                        'text-primary bg-primary/5' => $contactActive,
                        'text-muted hover:text-foreground' => ! $contactActive,
                    ])
                    @if ($contactActive) aria-current="page" @endif
                >
                    Contato
                </a>
            </div>
        </nav>

        {{-- Search (lg+) --}}
        <form method="GET" action="{{ route('search.index') }}" class="hidden shrink-0 lg:block">
            <label for="site-search-query" class="sr-only">Buscar no blog</label>
            <div class="relative">
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75"
                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted">
                    <circle cx="9" cy="9" r="5.5" /><path d="m14 14 3 3" stroke-linecap="round" />
                </svg>
                <input
                    id="site-search-query" name="q" type="search" placeholder="Buscar..."
                    class="h-10 w-48 rounded-control border border-line bg-surface pl-9 pr-3 text-sm text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-secondary xl:w-56"
                >
            </div>
        </form>

        {{-- Site auth (login/cadastro/usuário logado) --}}
        <x-site.auth-menu />

        {{-- Mobile menu trigger --}}
        <button
            x-ref="menuTrigger"
            type="button"
            @click="mobileOpen = ! mobileOpen"
            :aria-expanded="mobileOpen"
            aria-controls="mobile-site-navigation"
            class="ml-auto inline-flex size-11 shrink-0 items-center justify-center rounded-control text-foreground transition duration-200 ease-standard hover:bg-surface md:hidden"
        >
            <span class="sr-only">Abrir navegação</span>
            <svg x-show="!mobileOpen" aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" class="size-5">
                <path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round" />
            </svg>
            <svg x-show="mobileOpen" x-cloak aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" class="size-5">
                <path d="M5 5l10 10M15 5L5 15" stroke-linecap="round" />
            </svg>
        </button>
    </div>

    {{-- Mobile menu --}}
    <nav
        id="mobile-site-navigation"
        x-cloak
        x-show="mobileOpen"
        x-transition:enter="transition duration-200 ease-standard"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        @click.outside="closeMobile(true)"
        @keyup.escape.window="closeMobile(true)"
        aria-label="Navegação móvel"
        class="border-t border-line bg-surface-raised md:hidden"
    >
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6">
            {{-- Search on mobile --}}
            <form method="GET" action="{{ route('search.index') }}" class="mb-3">
                <label for="mobile-search-query" class="sr-only">Buscar no blog</label>
                <div class="relative">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75"
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted">
                        <circle cx="9" cy="9" r="5.5" /><path d="m14 14 3 3" stroke-linecap="round" />
                    </svg>
                    <input
                        id="mobile-search-query" name="q" type="search" placeholder="Buscar no blog..."
                        class="h-11 w-full rounded-control border border-line bg-surface pl-9 pr-3 text-sm text-foreground placeholder:text-muted"
                    >
                </div>
            </form>

            {{-- Primary links --}}
            <div class="grid grid-cols-2 gap-1">
                <a href="{{ route('home') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Início</a>
                <a href="{{ route('products.index') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Produtos</a>
                <a href="{{ route('blog.index') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Blog</a>
                <a href="{{ route('site.contact') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Contato</a>
            </div>

            {{-- Categorias do catálogo --}}
            @if (($navigationCatalogCategories ?? collect())->count() > 0)
                <div class="mt-3 border-t border-line pt-3">
                    <p class="px-3 pb-2 text-xs uppercase tracking-wider text-muted">Categorias</p>
                    <ul class="grid grid-cols-2 gap-1">
                        @foreach ($navigationCatalogCategories as $cat)
                            <li>
                                <a
                                    href="{{ route('catalog-categories.show', $cat->slug) }}"
                                    @click="closeMobile()"
                                    class="block rounded-control px-3 py-2 text-sm text-foreground hover:bg-surface"
                                >
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Categorias do blog --}}
            @if (($navigationCategories ?? collect())->count() > 0)
                <div class="mt-3 border-t border-line pt-3">
                    <p class="px-3 pb-2 text-xs uppercase tracking-wider text-muted">Categorias do blog</p>
                    <ul class="grid grid-cols-2 gap-1">
                        @foreach ($navigationCategories as $category)
                            <li>
                                <a
                                    href="{{ route('categories.show', $category->slug) }}"
                                    @click="closeMobile()"
                                    class="block rounded-control px-3 py-2 text-sm text-foreground hover:bg-surface"
                                >
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @auth
                <div class="mt-3 border-t border-line pt-3">
                    <a href="{{ route('site.profile.edit') }}" @click="closeMobile()" class="block rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Meu perfil</a>
                    @if (auth()->user()?->isAdmin())
                        <a href="{{ url('/admin') }}" @click="closeMobile()" class="block rounded-control px-3 py-2.5 text-sm font-semibold text-primary hover:bg-surface">Painel admin</a>
                    @endif
                    <form method="POST" action="{{ route('site.logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-control px-3 py-2.5 text-left text-sm font-semibold text-foreground hover:bg-surface">Sair</button>
                    </form>
                </div>
            @else
                <div class="mt-3 grid grid-cols-2 gap-1 border-t border-line pt-3">
                    <a href="{{ route('site.login') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Entrar</a>
                    <a href="{{ route('site.register') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-primary hover:bg-surface">Cadastre-se</a>
                </div>
            @endauth
        </div>
    </nav>
</header>