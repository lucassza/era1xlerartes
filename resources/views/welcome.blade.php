<x-layouts.app :meta="$meta" :json-ld="$jsonLd">
    <div class="mx-auto max-w-7xl space-y-12 px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <section id="destaques" aria-labelledby="destaques-title" class="scroll-mt-6">
            <h2 id="destaques-title" class="sr-only">Destaques editoriais</h2>
            <x-ui.card :post="$heroPost" variant="hero" />

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                @forelse ($featuredPosts as $post)
                    <x-ui.card :post="$post" variant="featured" />
                @empty
                    <x-ui.empty-state title="Sem destaques por enquanto" description="Novos conteúdos em destaque aparecerão aqui quando forem publicados." class="md:col-span-2" />
                @endforelse
            </div>
        </section>

        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="space-y-12">
                <section id="posts-recentes" aria-labelledby="posts-recentes-title" class="scroll-mt-6">
                    <x-ui.section-heading
                        id="posts-recentes-title"
                        label="Em dia"
                        title="Posts recentes"
                    />

                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse ($listingPosts as $post)
                            <x-ui.card :post="$post" variant="grid" />
                        @empty
                            <x-ui.empty-state title="Ainda não há posts recentes" description="Volte em breve para acompanhar as próximas publicações." class="sm:col-span-2 xl:col-span-3" />
                        @endforelse
                    </div>
                </section>

            @foreach ($categoryTopics as $item)
                <section id="{{ $item['category']->slug }}" aria-labelledby="{{ $item['category']->slug }}-title" class="scroll-mt-6">
                    <x-ui.section-heading :id="$item['category']->slug.'-title'" :title="$item['category']->name" border />

                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach ($item['posts'] as $post)
                            <x-ui.card :post="$post" variant="grid" />
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if ($testimonialsEnabled && $testimonials->isNotEmpty())
                <x-site.testimonials-section :testimonials="$testimonials" :show-form="false" />
            @endif
        </div>

            <aside class="space-y-6 lg:sticky lg:top-6">
                <section id="populares" aria-labelledby="populares-title" class="scroll-mt-6 overflow-hidden rounded-card border border-line bg-surface-raised shadow-card">
                    <div class="bg-gradient-to-r from-primary/[0.08] to-secondary/[0.04] px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 id="populares-title" class="font-display text-xl font-bold text-foreground">Mais populares</h2>
                            <span class="inline-flex items-center rounded-pill bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">Ranking</span>
                        </div>
                    </div>
                    <div class="space-y-3 p-5">
                        @forelse ($popularPosts as $post)
                            <x-ui.card :post="$post" variant="ranked" :rank="$loop->iteration" />
                        @empty
                            <x-ui.empty-state title="Sem ranking ainda" description="A popularidade será exibida assim que houver dados suficientes." />
                        @endforelse
                    </div>
                </section>

                <x-forms.newsletter :enabled="$newsletterEnabled" />
            </aside>
        </div>
    </div>
</x-layouts.app>
