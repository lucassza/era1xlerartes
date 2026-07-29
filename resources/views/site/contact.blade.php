@php
    $meta = [
        'title' => 'Contato | '.config('app.name', 'Era1x Ler Artes'),
        'description' => 'Entre em contato com a equipe comercial do catálogo de produtos.',
        'canonical' => route('site.contact'),
        'robots' => 'index,follow',
        'og' => [
            'title' => 'Contato',
            'description' => 'Entre em contato com a equipe comercial do catálogo.',
            'type' => 'website',
            'url' => route('site.contact'),
        ],
        'twitter' => [
            'card' => 'summary',
            'title' => 'Contato',
            'description' => 'Entre em contato com a equipe comercial do catálogo.',
        ],
    ];
    $settings = app(\App\Services\CatalogCacheService::class)->settings();
    $phone = $settings->getAttribute('commercial_phone');
    $whatsapp = $settings->getAttribute('commercial_whatsapp');
    $recipients = $settings->getAttribute('inquiry_recipients');
@endphp

<x-layouts.app :meta="$meta">
    <main id="main" class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-foreground">Contato</h1>
            <p class="mt-1 text-base text-muted">Para dúvidas sobre produtos, cotações ou parcerias comerciais, entre em contato pelos canais abaixo.</p>
        </header>

        <section class="space-y-6 rounded-card border border-line bg-surface-raised p-6">
            @if (filled($whatsapp))
                <div>
                    <h2 class="text-sm font-semibold text-foreground">WhatsApp</h2>
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) $whatsapp) }}" target="_blank" rel="noopener" class="mt-1 inline-flex text-base font-semibold text-primary hover:underline">{{ $whatsapp }}</a>
                </div>
            @endif

            @if (filled($phone))
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Telefone</h2>
                    <p class="mt-1 text-base text-foreground">{{ $phone }}</p>
                </div>
            @endif

            @if (filled($recipients))
                <div>
                    <h2 class="text-sm font-semibold text-foreground">E-mails</h2>
                    <p class="mt-1 text-base text-foreground">{{ $recipients }}</p>
                </div>
            @endif

            <p class="text-sm text-muted">
                Para falar sobre um produto específico, use o botão <strong>Consulte</strong> em qualquer página de produto. Sua mensagem é registrada em nosso sistema e respondida pela equipe comercial.
            </p>
        </section>
    </main>
</x-layouts.app>