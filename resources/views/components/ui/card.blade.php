@props([
    'post' => null,
    'variant' => 'grid',
    'rank' => null,
    'showViews' => false,
])

@php
    $cover = $post?->getFirstMedia('cover');
    $coverUrl = null;
    $coverSrcset = null;

    if ($cover) {
        $coverUrl = $cover->hasGeneratedConversion('thumb-1200') ? $cover->getUrl('thumb-1200') : $cover->getUrl();
        $coverSrcset = $cover->hasResponsiveImages('thumb-1200') ? $cover->getSrcset('thumb-1200') : null;
    }

    // Category image as fallback when post has no cover
    if (! $coverUrl && $post?->category) {
        $catMedia = $post->category->getFirstMedia('image');
        if ($catMedia) {
            $coverUrl = $catMedia->hasGeneratedConversion('thumb-1200') ? $catMedia->getUrl('thumb-1200') : $catMedia->getUrl();
        }
    }

    $readingTime = $post?->readingTime();
    $date = optional($post?->published_at)?->format('d/m/Y');
    $category = $post?->category?->name ?? 'Editorial';
    $postUrl = $post ? route('posts.show', $post->slug) : '#';
    $categoryInitials = mb_strtoupper(mb_substr($category, 0, 2));
@endphp

@if ($variant === 'hero')

    {{-- Hero: large editorial split layout —————————————————————————————————— --}}
    <article class="overflow-hidden rounded-feature border border-line bg-surface-raised shadow-editorial">
        <div class="grid min-h-[26rem] lg:min-h-[32rem] lg:grid-cols-[1fr_45%]">

            {{-- Text side with subtle gradient accent --}}
            <div class="relative flex flex-col justify-center overflow-hidden p-8 sm:p-12 lg:p-14">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-primary/[0.06] via-transparent to-secondary/[0.03]"></div>
                <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-primary via-secondary to-transparent"></div>

                <div class="relative">
                    @if ($post)
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="inline-flex items-center rounded-pill bg-primary px-3 py-1 text-xs font-bold text-white shadow-sm">
                                {{ $category }}
                            </span>
                            @if ($date)
                                <span class="text-sm font-medium text-secondary">{{ $date }}</span>
                            @endif
                            @if ($readingTime)
                                <span class="text-sm text-muted">{{ $readingTime }} min de leitura</span>
                            @endif
                        </div>

                        <h2 class="mt-4 font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                            <a href="{{ $postUrl }}" class="transition duration-200 ease-standard hover:text-primary">
                                {{ $post->title }}
                            </a>
                        </h2>

                        @if ($post->excerpt)
                            <p class="mt-4 max-w-prose text-lg leading-8 text-muted">{{ $post->excerpt }}</p>
                        @endif

                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a href="{{ $postUrl }}" class="inline-flex items-center gap-2 rounded-control bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-200 ease-standard hover:bg-primary-strong">
                                Ler artigo
                                <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            @if ($post->author?->name)
                                <span class="text-sm text-muted">por <strong class="font-semibold text-foreground">{{ $post->author->name }}</strong></span>
                            @endif
                        </div>
                    @else
                        <span class="inline-flex items-center rounded-pill bg-primary px-3 py-1 text-xs font-bold text-white">Era1x Ler Artes</span>
                        <h2 class="mt-4 font-display text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                            Conteúdo que acompanha o seu ritmo.
                        </h2>
                        <p class="mt-5 max-w-prose text-lg leading-8 text-muted">
                            Acompanhe histórias, tutoriais e novidades da comunidade Era1x Ler Artes.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Image side --}}
            <div class="relative min-h-64 bg-surface lg:min-h-0">
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1024px) 45vw, 100vw" @endif
                        alt="Destaque: {{ $post?->title }}"
                        class="absolute inset-0 size-full object-cover"
                        loading="eager"
                        fetchpriority="high"
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-surface-raised/20 to-transparent lg:from-transparent"></div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-surface to-primary/[0.04]">
                        <span aria-hidden="true" class="select-none font-display text-[8rem] font-bold leading-none text-primary/10">Era1x</span>
                    </div>
                @endif
            </div>

        </div>
    </article>

@elseif ($variant === 'featured')

    {{-- Featured: medium card with solid category badge ————————————————————— --}}
    <article class="group overflow-hidden rounded-card border border-line bg-surface-raised shadow-card transition duration-200 ease-standard hover:-translate-y-0.5 hover:shadow-float">
        <a href="{{ $postUrl }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-secondary focus-visible:ring-offset-2">
            <div class="relative aspect-[16/9] overflow-hidden bg-surface">
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 768px) 50vw, 100vw" @endif
                        alt="Capa: {{ $post->title }}"
                        width="1200" height="675"
                        loading="lazy" decoding="async"
                        class="size-full object-cover transition duration-300 ease-standard group-hover:scale-[1.02]"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-surface to-primary/[0.06]">
                        <span aria-hidden="true" class="select-none font-display text-6xl font-bold text-primary/20">
                            {{ $categoryInitials }}
                        </span>
                    </div>
                @endif
                <span class="absolute left-3 top-3 inline-flex items-center rounded-pill bg-primary px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                    {{ $category }}
                </span>
            </div>
            <div class="p-5">
                @if ($date)
                    <p class="text-xs font-medium text-secondary">{{ $date }}</p>
                @endif
                <h3 class="mt-1.5 font-display text-xl font-bold text-foreground transition duration-200 group-hover:text-primary">
                    {{ $post->title }}
                </h3>
                @if ($post->excerpt)
                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-muted">{{ $post->excerpt }}</p>
                @endif
            </div>
        </a>
    </article>

@elseif ($variant === 'grid')

    {{-- Grid: standard post card ——————————————————————————————————————————— --}}
    <article class="group overflow-hidden rounded-card border border-line bg-surface-raised shadow-card transition duration-200 ease-standard hover:-translate-y-0.5 hover:shadow-float">
        <a href="{{ $postUrl }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-secondary focus-visible:ring-offset-2">
            <div class="relative aspect-[16/10] overflow-hidden bg-surface">
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1280px) 25vw, (min-width: 640px) 50vw, 100vw" @endif
                        alt="Capa: {{ $post->title }}"
                        width="600" height="375"
                        loading="lazy" decoding="async"
                        class="size-full object-cover transition duration-300 ease-standard group-hover:scale-[1.02]"
                    >
                @else
                    <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-surface to-primary/[0.06]">
                        <span aria-hidden="true" class="select-none font-display text-5xl font-bold text-primary/20">
                            {{ $categoryInitials }}
                        </span>
                    </div>
                @endif
                <span class="absolute left-3 top-3 inline-flex items-center rounded-pill bg-primary px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                    {{ $category }}
                </span>
            </div>
            <div class="p-5">
                @if ($date)
                    <p class="text-xs font-medium text-secondary">{{ $date }}</p>
                @endif
                <h3 class="mt-1.5 font-display text-xl font-bold text-foreground transition duration-200 group-hover:text-primary">
                    {{ $post->title }}
                </h3>
                @if ($post->excerpt)
                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-muted">{{ $post->excerpt }}</p>
                @endif
                @if ($showViews)
                    <p class="mt-3 text-xs font-semibold text-muted">{{ number_format($post->views_count) }} visualizações</p>
                @endif
            </div>
        </a>
    </article>

@elseif ($variant === 'list')

    {{-- List: taxonomy/search card ————————————————————————————————————————— --}}
    <article class="group overflow-hidden rounded-card border border-line bg-surface-raised transition duration-200 ease-standard hover:border-primary/40 hover:shadow-card">
        <a href="{{ $postUrl }}" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-secondary focus-visible:ring-offset-2">
            <div class="relative aspect-[3/2] overflow-hidden bg-surface">
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        @if ($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw" @endif
                        alt="Capa: {{ $post->title }}"
                        width="600" height="400"
                        loading="lazy" decoding="async"
                        class="size-full object-cover transition duration-300 ease-standard group-hover:scale-[1.02]"
                    >
                @else
                    <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-surface to-primary/[0.06]">
                        <span aria-hidden="true" class="select-none font-display text-5xl font-bold text-primary/20">
                            {{ $categoryInitials }}
                        </span>
                    </div>
                @endif
                <span class="absolute left-3 top-3 inline-flex items-center rounded-pill bg-primary px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                    {{ $category }}
                </span>
            </div>
            <div class="p-5 sm:p-6">
                @if ($date)
                    <p class="text-xs font-medium text-secondary">{{ $date }}</p>
                @endif
                <h2 class="mt-1.5 font-display text-xl font-bold text-foreground transition duration-200 group-hover:text-primary">
                    {{ $post->title }}
                </h2>
                @if ($post->excerpt)
                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-muted">{{ $post->excerpt }}</p>
                @endif
            </div>
        </a>
    </article>

@elseif ($variant === 'ranked')

    {{-- Ranked: sidebar ranking item —————————————————————————————————————— --}}
    <article class="border-b border-line pb-3 last:border-b-0 last:pb-0">
        <a href="{{ $postUrl }}" class="group grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3 rounded-control p-1.5 transition duration-200 ease-standard hover:bg-surface">
            <span aria-hidden="true" class="font-display text-2xl font-bold leading-tight text-primary">
                {{ str_pad((string) $rank, 2, '0', STR_PAD_LEFT) }}
            </span>
            <span>
                <span class="block text-xs font-medium text-secondary">
                    {{ $category }}@if ($date) · {{ $date }}@endif
                </span>
                <span class="mt-1 block font-display font-bold text-foreground transition duration-200 group-hover:text-primary">
                    {{ $post->title }}
                </span>
                @if ($post->excerpt)
                    <span class="mt-1 block line-clamp-2 text-sm leading-5 text-muted">{{ $post->excerpt }}</span>
                @endif
            </span>
        </a>
    </article>

@endif
