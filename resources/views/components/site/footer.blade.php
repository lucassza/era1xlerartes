<footer class="border-t border-line bg-surface-raised">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto] lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="inline-block rounded-control" aria-label="TDB Mix Blog — página inicial">
                <img src="{{ asset('images/brand/logo-peq.png') }}" alt="TDB Mix Blog" width="160" height="80" class="h-14 w-28 object-contain mix-blend-multiply">
            </a>
            <p class="mt-3 max-w-md text-sm leading-6 text-muted">Histórias, novidades e conteúdo editorial do universo TDB Mix.</p>
        </div>

        <nav aria-label="Links do rodapé" class="flex flex-wrap content-start gap-x-5 gap-y-3 text-sm font-semibold">
            <a href="{{ route('home') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Início</a>
            <a href="{{ route('testimonials.index') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Depoimentos</a>
            <a href="{{ route('search.index') }}" class="rounded-control text-foreground underline-offset-4 hover:underline">Busca</a>
            <span class="cursor-not-allowed text-muted" aria-disabled="true">Newsletter em breve</span>
            <span class="cursor-not-allowed text-muted" aria-disabled="true">Contato em breve</span>
        </nav>
    </div>
</footer>
