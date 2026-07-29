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
@endphp

@if ($enabled && ! $isEmpty)
    @php
        $containerAlign = $alignment === \App\Models\CatalogSetting::HERO_ALIGNMENT_CENTER
            ? 'items-center text-center'
            : 'items-start text-left';
        $contentMaxWidth = $alignment === \App\Models\CatalogSetting::HERO_ALIGNMENT_CENTER
            ? 'mx-auto max-w-3xl'
            : 'max-w-3xl';
    @endphp

    <section
        aria-label="Destaque principal do catálogo"
        @class([
            'relative isolate overflow-hidden rounded-feature border border-line',
            'min-h-[18rem] sm:min-h-[22rem] lg:min-h-[26rem]',
        ])
    >
        @if ($hasImage)
            <img
                src="{{ $imageUrl }}"
                alt=""
                loading="eager"
                decoding="async"
                class="absolute inset-0 -z-10 size-full object-cover"
            >
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary/85 via-primary/55 to-secondary/40" aria-hidden="true"></div>
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/45 via-transparent to-transparent" aria-hidden="true"></div>
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary via-primary-strong to-secondary" aria-hidden="true"></div>
            <div class="absolute inset-0 -z-10 opacity-30 [background-image:radial-gradient(circle_at_20%_20%,white_0,transparent_40%),radial-gradient(circle_at_80%_60%,white_0,transparent_35%)]" aria-hidden="true"></div>
        @endif

        <div @class([
            'relative flex h-full min-h-[18rem] flex-col justify-center px-6 py-12 sm:min-h-[22rem] sm:px-10 sm:py-16 lg:min-h-[26rem] lg:px-14 lg:py-20',
            $containerAlign,
        ])>
            <div class="{{ $contentMaxWidth }}">
                @if ($title !== '')
                    <h1 class="font-display text-4xl font-bold tracking-tight text-white drop-shadow-sm sm:text-5xl lg:text-6xl">
                        {{ $title }}
                    </h1>
                @endif

                @if ($subtitle !== '')
                    <p class="mt-4 max-w-prose text-base leading-relaxed text-white/90 sm:text-lg">
                        {{ $subtitle }}
                    </p>
                @endif

                @if ($showCta)
                    <div class="mt-8">
                        <a
                            href="{{ $ctaUrl }}"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-control bg-white px-6 text-base font-semibold text-primary shadow-card transition duration-200 ease-standard hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-white/70"
                        >
                            {{ $ctaLabel }}
                            <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                <path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif