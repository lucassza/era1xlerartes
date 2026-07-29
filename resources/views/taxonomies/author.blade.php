<x-layouts.app :title="$title">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ui.section-heading as="h1" label="Autor" :title="$title" :description="$description" />

        @if ($posts->isEmpty())
            <x-ui.empty-state title="Nenhum post deste autor" description="Este autor ainda não possui posts publicados." />
        @else
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li><x-ui.card :post="$post" variant="list" /></li>
                @endforeach
            </ul>

            <x-ui.pagination :paginator="$posts" />
        @endif
    </div>
</x-layouts.app>
