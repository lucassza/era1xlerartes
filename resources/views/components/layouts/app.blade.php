<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $meta['title'] ?? $title ?? config('app.name', 'Era1x Ler Artes') }}</title>
        <meta name="description" content="{{ $meta['description'] ?? 'Blog moderno em Laravel.' }}">
        <meta name="robots" content="{{ $meta['robots'] ?? 'index,follow' }}">
        <meta name="theme-color" content="#fcfafa">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('favicon-96x96.png') }}" sizes="96x96" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <link rel="alternate" type="application/rss+xml" title="{{ config('app.name', 'Era1x Ler Artes') }} RSS" href="{{ route('seo.rss') }}">

        @if (! empty($meta['canonical']))
            <link rel="canonical" href="{{ $meta['canonical'] }}">
        @endif

        @if (! empty($meta['og']))
            <meta property="og:title" content="{{ $meta['og']['title'] ?? '' }}">
            <meta property="og:description" content="{{ $meta['og']['description'] ?? '' }}">
            <meta property="og:type" content="{{ $meta['og']['type'] ?? 'website' }}">
            <meta property="og:url" content="{{ $meta['og']['url'] ?? '' }}">
            @if (! empty($meta['og']['image']))
                <meta property="og:image" content="{{ $meta['og']['image'] }}">
            @endif
        @endif

        @if (! empty($meta['twitter']))
            <meta name="twitter:card" content="{{ $meta['twitter']['card'] ?? 'summary' }}">
            <meta name="twitter:title" content="{{ $meta['twitter']['title'] ?? '' }}">
            <meta name="twitter:description" content="{{ $meta['twitter']['description'] ?? '' }}">
            @if (! empty($meta['twitter']['image']))
                <meta name="twitter:image" content="{{ $meta['twitter']['image'] }}">
            @endif
        @endif

        @if (! empty($jsonLd))
            <script type="application/ld+json">@json($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
        @endif

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-canvas text-foreground antialiased">
        <a
            href="#main-content"
            class="sr-only absolute left-4 top-4 z-50 -translate-y-full rounded-control bg-primary px-4 py-3 font-semibold text-white transition-transform focus:not-sr-only focus:translate-y-0"
        >
            Pular para o conteúdo principal
        </a>

        <x-site.header />

        <main id="main-content" tabindex="-1">
            {{ $slot }}
        </main>

        <x-site.footer />
        <x-site.search-dialog />

        @php
            $route = request()->route();
            $routeName = $route?->getName() ?? '';
            $inCatalog = str_starts_with($routeName, 'products.') || str_starts_with($routeName, 'catalog-categories.') || $routeName === 'home';
        @endphp
        @if ($inCatalog)
            @php $catalogSettings = app(\App\Services\CatalogCacheService::class)->settings(); @endphp
            @include('catalog._inquiry-modal', ['product' => null, 'settings' => $catalogSettings])
        @endif
    </body>

    @stack('scripts')
</html>
