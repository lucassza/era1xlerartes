@php
    /** @var \App\Services\CatalogCacheService $catalog */
    $catalog = app(\App\Services\CatalogCacheService::class);
    $catalogSettings = $footerCatalogSettings ?? $catalog->settings();
    $catalogCategories = $footerCatalogCategories ?? $catalog->activeCategories()->flatten()->take(6);
    $phone = $catalogSettings->getAttribute('commercial_phone');
    $whatsapp = $catalogSettings->getAttribute('commercial_whatsapp');
    $emails = $catalogSettings->getAttribute('inquiry_recipients');
    $hasContact = filled($phone) || filled($whatsapp) || filled($emails);
@endphp

<footer class="bg-primary text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div class="flex flex-col gap-4">
            <a href="{{ route('home') }}" class="inline-block w-fit rounded-control focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent" aria-label="{{ config('app.name', 'Era1x Ler Artes') }} — página inicial">
                <img src="{{ asset('images/brand/logo-era1x.png') }}" alt="{{ config('app.name', 'Era1x Ler Artes') }}" width="500" height="500" class="size-16 rounded-card bg-surface-raised object-contain p-1.5">
            </a>
            <p class="max-w-md text-sm leading-6 text-accent">
                {{ $catalogSettings->getAttribute('intro_text') ?: 'Catálogo de produtos com histórias, novidades e conteúdo editorial.' }}
            </p>
        </div>

        <nav aria-label="Links do rodapé" class="flex flex-col gap-3">
            <h2 class="text-xs font-bold uppercase tracking-[0.09em] text-accent">Institucional</h2>
            <a href="{{ route('home') }}" class="text-sm text-white underline-offset-4 hover:underline">Início</a>
            <a href="{{ route('products.index') }}" class="text-sm text-white underline-offset-4 hover:underline">Produtos</a>
            <a href="{{ route('blog.index') }}" class="text-sm text-white underline-offset-4 hover:underline">Blog</a>
            <a href="{{ route('site.contact') }}" class="text-sm text-white underline-offset-4 hover:underline">Contato</a>
        </nav>

        <nav aria-label="Categorias do catálogo" class="flex flex-col gap-3">
            <h2 class="text-xs font-bold uppercase tracking-[0.09em] text-accent">Catálogo</h2>
            @forelse ($catalogCategories as $cat)
                <a href="{{ route('catalog-categories.show', $cat->slug) }}" class="text-sm text-white underline-offset-4 hover:underline">{{ $cat->name }}</a>
            @empty
                <span class="text-sm text-accent">Sem categorias ainda.</span>
            @endforelse
        </nav>

        @if ($hasContact)
            <div class="flex flex-col gap-3">
                <h2 class="text-xs font-bold uppercase tracking-[0.09em] text-accent">Atendimento</h2>
                @if (filled($whatsapp))
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) $whatsapp) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center text-sm font-semibold text-white underline-offset-4 hover:underline">
                        WhatsApp {{ $whatsapp }}
                    </a>
                @endif
                @if (filled($phone))
                    <span class="inline-flex min-h-11 items-center text-sm text-white">Tel. {{ $phone }}</span>
                @endif
                @if (filled($emails))
                    <span class="inline-flex min-h-11 items-center text-sm break-words text-white">{{ $emails }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="border-t border-accent/30">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-5 text-xs text-accent sm:px-6 lg:px-8">
            <span>© {{ now()->year }} {{ config('app.name', 'Era1x Ler Artes') }}. Todos os direitos reservados.</span>
            <span>Catálogo para consulta — sem venda direta pelo site.</span>
        </div>
    </div>
</footer>
