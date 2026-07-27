<x-layouts.app :meta="$meta">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.section-heading
            as="h1"
            label="Explorar"
            title="Busca"
            description="Encontre posts publicados por título, slug ou conteúdo."
        />

        <form method="GET" action="{{ route('search.index') }}" class="mb-10 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
            <x-ui.input id="search-query" name="q" type="search" :value="$query" label="Buscar posts" placeholder="Digite palavras-chave" />
            <x-ui.button type="submit" class="w-full sm:w-auto">
                Buscar
            </x-ui.button>
        </form>

        @if ($query === '')
            <x-ui.empty-state title="Comece uma busca" description="Digite um termo no campo acima para encontrar posts publicados." />
        @elseif ($results->isEmpty())
            <x-ui.empty-state title="Nenhum resultado encontrado" description="Não encontramos posts para &quot;{{ $query }}&quot;. Tente usar outros termos." />
        @else
            <section aria-labelledby="search-results-title">
                <h2 id="search-results-title" class="mb-5 font-display text-2xl font-bold text-foreground">
                    Resultados para "{{ $query }}"
                </h2>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($results as $post)
                        <li><x-ui.card :post="$post" variant="list" /></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layouts.app>
