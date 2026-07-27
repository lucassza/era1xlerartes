@props(['heading', 'posts', 'showViews' => false])

@if ($posts->isNotEmpty())
    <section aria-labelledby="{{ \Illuminate\Support\Str::slug($heading) }}" class="mt-10 border-t border-line pt-8">
        <h2 id="{{ \Illuminate\Support\Str::slug($heading) }}" class="mb-5 font-display text-2xl font-bold tracking-tight text-foreground">
            {{ $heading }}
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($posts as $relatedPost)
                <x-ui.card :post="$relatedPost" variant="grid" :show-views="$showViews" />
            @endforeach
        </div>
    </section>
@endif
