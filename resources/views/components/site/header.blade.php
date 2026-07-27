@php
    $currentSlug = request()->route('slug');
    // $navigationCategories is injected by the view composer in AppServiceProvider
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
        <a href="{{ route('home') }}" class="shrink-0 rounded-control" aria-label="TDB Mix Blog — página inicial">
            <img src="{{ asset('images/brand/logo-peq.png') }}" alt="TDB Mix Blog" width="160" height="80"
                class="h-10 w-20 object-contain mix-blend-multiply sm:h-12 sm:w-24">
        </a>

        {{-- Category nav (desktop): scrollable, min-w-0 flex-1 so it doesn't push logo/search --}}
        <nav aria-label="Navegação principal" class="hidden min-w-0 flex-1 md:block">
            <div class="flex items-center gap-0.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <a
                    href="{{ route('home') }}"
                    class="shrink-0 rounded-control px-3 py-2 text-sm font-semibold text-muted transition duration-200 ease-standard hover:bg-surface hover:text-foreground"
                >
                    Início
                </a>

                @foreach ($navigationCategories ?? [] as $category)
                    <a
                        href="{{ route('categories.show', $category->slug) }}"
                        @class([
                            'shrink-0 rounded-control px-3 py-2 text-sm font-semibold transition duration-200 ease-standard hover:bg-surface',
                            'text-primary bg-primary/5' => $currentSlug === $category->slug,
                            'text-muted hover:text-foreground' => $currentSlug !== $category->slug,
                        ])
                        @if ($currentSlug === $category->slug) aria-current="page" @endif
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
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
                    class="h-10 w-48 rounded-control border border-line bg-surface pl-9 pr-3 text-sm text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-accent xl:w-56"
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

            {{-- Links --}}
            <div class="grid grid-cols-2 gap-1">
                <a href="{{ route('home') }}" @click="closeMobile()" class="col-span-2 rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">
                    Início
                </a>
                <a href="{{ route('testimonials.index') }}" @click="closeMobile()" class="col-span-2 rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">
                    Depoimentos
                </a>
                @foreach ($navigationCategories ?? [] as $category)
                    <a
                        href="{{ route('categories.show', $category->slug) }}"
                        @click="closeMobile()"
                        @class([
                            'rounded-control px-3 py-2.5 text-sm font-semibold transition hover:bg-surface',
                            'text-primary' => $currentSlug === $category->slug,
                            'text-foreground' => $currentSlug !== $category->slug,
                        ])
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
                @auth
                    <a href="{{ route('site.profile.edit') }}" @click="closeMobile()" class="col-span-2 rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Meu perfil</a>
                    @if (auth()->user()?->isAdmin())
                        <a href="{{ url('/admin') }}" @click="closeMobile()" class="col-span-2 rounded-control px-3 py-2.5 text-sm font-semibold text-primary hover:bg-surface">Painel admin</a>
                    @endif
                    <form method="POST" action="{{ route('site.logout') }}" class="col-span-2">
                        @csrf
                        <button type="submit" class="w-full rounded-control px-3 py-2.5 text-left text-sm font-semibold text-foreground hover:bg-surface">Sair</button>
                    </form>
                @else
                    <a href="{{ route('site.login') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-surface">Entrar</a>
                    <a href="{{ route('site.register') }}" @click="closeMobile()" class="rounded-control px-3 py-2.5 text-sm font-semibold text-primary hover:bg-surface">Cadastre-se</a>
                @endauth
            </div>
        </div>
    </nav>
</header>
