@props(['author'])

@if ($author)
    @php($avatarUrl = $author->getFirstMediaUrl('avatar', 'thumb-300') ?: $author->getFirstMediaUrl('avatar'))
    <section aria-labelledby="author-heading" class="mt-10 rounded-feature border border-line bg-surface p-6 sm:p-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
            @if (filled($avatarUrl))
                <img src="{{ $avatarUrl }}" alt="" width="96" height="96" class="size-20 rounded-full object-cover sm:size-24">
            @else
                <div aria-hidden="true" class="flex size-20 items-center justify-center rounded-full bg-primary/10 font-display text-2xl font-bold text-primary sm:size-24">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($author->name, 0, 1)) }}</div>
            @endif
            <div>
                <p class="text-sm font-semibold text-muted">Sobre o autor</p>
                <h2 id="author-heading" class="mt-1 font-display text-2xl font-bold text-foreground"><a href="{{ route('authors.show', $author->slug) }}" class="rounded underline-offset-4 hover:text-primary hover:underline">{{ $author->name }}</a></h2>
                @if (filled($author->bio))
                    <p class="mt-3 leading-7 text-muted">{{ $author->bio }}</p>
                @endif
            </div>
        </div>
    </section>
@endif
