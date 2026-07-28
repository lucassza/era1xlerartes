@php
    /** @var \App\Services\CatalogCacheService $catalog */
    $catalog = app(\App\Services\CatalogCacheService::class);
    $catalogSettings = $footerCatalogSettings ?? $catalog->settings();
    $catalogCategories = $footerCatalogCategories ?? $catalog->activeCategories()->flatten()->take(6);
    $phone = $catalogSettings->getAttribute('commercial_phone');
    $whatsapp = $catalogSettings->getAttribute('commercial_whatsapp');
    $emails = $catalogSettings->getAttribute('inquiry_recipients');
@endphp

<footer class="border-t border-line bg-surface-raised">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto_auto] lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="inline-block rounded-control" aria-label="{{ config('app.name', 'TDB Mix Blog') }} — página inicial">
                <img src="{{ asset('images/brand/logo-peq.png') }}" alt="{{ config('app.name', 'TDB Mix Blog') }}" width="160" height="80" class="h-14 w-28 object-contain mix-blend-multiply">
            </a>
            <p class="mt-3 max-w-md text-sm leading-6 text-muted">{{ $catalogSettings->getAttribute('intro_text') ?: 'Catálogo de produtos com histórias, novidades e conteúdo editorial.' }}</p>
        </div>

        <nav aria-label="Links do rodapé" class="flex flex-col gap-3 text-sm">
            <h2 class="text-xs uppercase tracking-wider text-muted">Catálogo</h2>
            <a href="{{ route('home') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Início</a>
            <a href="{{ route('products.index') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Produtos</a>
            <a href="{{ route('blog.index') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Blog</a>
            <a href="{{ route('site.contact') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Contato</a>
        </nav>

        <nav aria-label="Categorias do catálogo" class="flex flex-col gap-3 text-sm">
            <h2 class="text-xs uppercase tracking-wider text-muted">Categorias</h2>
            @forelse ($catalogCategories as $cat)
                <a href="{{ route('catalog-categories.show', $cat->slug) }}" class="rounded-control text-foreground underline-offset-4 hover:underline">{{ $cat->name }}</a>
            @empty
                <span class="text-muted">Sem categorias ainda.</span>
            @endforelse
        </nav>
    </div>

    @if (filled($phone) || filled($whatsapp) || filled($emails))
        <div class="border-t border-line">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-4 text-xs text-muted sm:px-6 lg:px-8">
                @if (filled($whatsapp))
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) $whatsapp) }}" target="_blank" rel="noopener" class="font-semibold text-primary hover:underline">WhatsApp: {{ $whatsapp }}</a>
                @endif
                @if (filled($phone))
                    <span>Tel: {{ $phone }}</span>
                @endif
                @if (filled($emails))
                    <span>E-mails: {{ $emails }}</span>
                @endif
            </div>
        </div>
    @endif
</footer>