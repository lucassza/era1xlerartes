@php
    /** @var \App\Models\CatalogSetting $settings */
    use Illuminate\Support\Facades\Storage;

    $enabled = (bool) $settings->getAttribute('hero_enabled');
    $title = trim((string) $settings->getAttribute('hero_title'));
    $subtitle = trim((string) $settings->getAttribute('hero_subtitle'));
    $ctaLabel = trim((string) $settings->getAttribute('hero_cta_label'));
    $ctaUrl = trim((string) $settings->getAttribute('hero_cta_url'));
    $imagePath = trim((string) $settings->getAttribute('hero_image_path'));
    $alignment = $settings->getAttribute('hero_alignment') === \App\Models\CatalogSetting::HERO_ALIGNMENT_CENTER
        ? \App\Models\CatalogSetting::HERO_ALIGNMENT_CENTER
        : \App\Models\CatalogSetting::HERO_ALIGNMENT_LEFT;

    $hasImage = $imagePath !== '';
    $imageUrl = $hasImage ? Storage::disk('public')->url($imagePath) : null;
    $showCta = $ctaLabel !== '' && $ctaUrl !== '';
    $isEmpty = $title === '' && $subtitle === '' && ! $showCta;
    $isCentered = $alignment === \App\Models\CatalogSetting::HERO_ALIGNMENT_CENTER;
@endphp

@if ($enabled && ! $isEmpty)
    <section
        aria-label="Destaque principal do catálogo"
        class="relative isolate overflow-hidden rounded-feature bg-primary"
    >
        @if ($hasImage)
            <img
                src="{{ $imageUrl }}"
                alt=""
                loading="eager"
                decoding="async"
                class="absolute inset-0 -z-10 size-full object-cover"
            >
            <div class="absolute inset-0 -z-10 bg-primary/80" aria-hidden="true"></div>
        @else
            {{-- Motivo geométrico discreto, no lugar de um degradê --}}
            <svg
                aria-hidden="true"
                viewBox="0 0 600 600"
                fill="none"
                stroke="currentColor"
                stroke-width="1.4"
                class="pointer-events-none absolute -bottom-32 -right-16 -z-10 size-[34rem] text-accent/20 sm:size-[38rem]"
            >
                <circle cx="300" cy="300" r="260" /><circle cx="300" cy="300" r="190" /><circle cx="300" cy="300" r="120" />
                <path d="M40 300h520M300 40v520" stroke-linecap="round" />
            </svg>
        @endif

        <div @class([
            'relative flex flex-col gap-6 px-6 py-12 sm:px-10 sm:py-16 lg:px-14 lg:py-20',
            'items-center text-center' => $isCentered,
            'items-start text-left' => ! $isCentered,
        ])>
            <div @class(['flex flex-col gap-5', 'mx-auto max-w-3xl' => $isCentered, 'max-w-3xl' => ! $isCentered])>
                <span @class([
                    'inline-flex h-8 items-center rounded-pill border border-accent/40 px-3.5 text-[11px] font-bold uppercase tracking-[0.08em] text-accent',
                    'self-center' => $isCentered,
                    'self-start' => ! $isCentered,
                ])>
                    Catálogo {{ config('app.name', 'Era1x Ler Artes') }}
                </span>

                @if ($title !== '')
                    <h1 class="font-display text-4xl font-bold tracking-tight text-white text-pretty sm:text-5xl lg:text-[3.375rem] lg:leading-[1.08]">
                        {{ $title }}
                    </h1>
                @endif

                @if ($subtitle !== '')
                    <p class="max-w-prose text-base leading-relaxed text-accent sm:text-lg">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            {{-- Busca do catálogo, dentro do herói --}}
            <form
                method="GET"
                action="{{ route('products.index') }}"
                role="search"
                @class(['w-full max-w-xl', 'mx-auto' => $isCentered])
            >
                <label for="hero-search" class="sr-only">Buscar produtos</label>
                <div class="relative flex items-center">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75"
                        class="pointer-events-none absolute left-5 size-5 text-muted">
                        <circle cx="9" cy="9" r="5.5" /><path d="m14 14 3 3" stroke-linecap="round" />
                    </svg>
                    <input
                        id="hero-search"
                        type="search"
                        name="buscar"
                        placeholder="O que você procura hoje?"
                        class="h-14 w-full rounded-pill border border-line bg-surface-raised pl-12 pr-32 text-sm text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-secondary sm:text-base"
                    >
                    <button type="submit" class="absolute right-1.5 inline-flex h-11 items-center justify-center rounded-pill bg-primary px-6 text-sm font-bold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
                        Buscar
                    </button>
                </div>
            </form>

            <div @class(['flex flex-wrap gap-3', 'justify-center' => $isCentered])>
                @if ($showCta)
                    <a
                        href="{{ $ctaUrl }}"
                        class="inline-flex min-h-13 items-center justify-center gap-2 rounded-control bg-surface-raised px-6 text-base font-bold text-primary shadow-card transition duration-200 ease-standard hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                    >
                        {{ $ctaLabel }}
                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" class="size-4">
                            <path d="M4 10h12M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                @endif

                <a
                    href="{{ route('site.contact') }}"
                    class="inline-flex min-h-13 items-center justify-center rounded-control border border-accent/45 px-6 text-base font-bold text-white transition duration-200 ease-standard hover:border-accent hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                >
                    Falar com a equipe
                </a>
            </div>
        </div>
    </section>
@endif
