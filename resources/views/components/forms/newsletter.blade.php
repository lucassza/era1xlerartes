@props(['enabled' => true])

<section aria-labelledby="newsletter-title" class="overflow-hidden rounded-card border border-line bg-surface-raised shadow-card">
    <div class="bg-gradient-to-r from-accent/[0.06] to-primary/[0.04] px-5 py-4">
        <h2 id="newsletter-title" class="font-display text-xl font-bold text-foreground">Receba novidades</h2>
        <p class="mt-1 text-sm leading-6 text-muted">Os melhores conteúdos direto na sua caixa de entrada.</p>
    </div>
    <div class="p-5">

    @if (session('newsletter_status'))
        <div role="status" aria-live="polite" class="mt-4 rounded-control border border-success/30 bg-success/10 px-4 py-3 text-sm text-foreground">
            {{ session('newsletter_status') }}
        </div>
    @endif

    @if ($enabled)
        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-4 space-y-3">
            @csrf
            <x-ui.input id="newsletter-email" name="email" type="email" label="Seu e-mail" :value="old('email')" :error="$errors->first('email')" autocomplete="email" required />
            <x-ui.button type="submit" class="w-full">Inscrever-se</x-ui.button>
        </form>
    @else
        <p class="mt-4 rounded-control bg-surface px-4 py-3 text-sm text-muted">A newsletter está temporariamente desabilitada.</p>
    @endif
    </div>
</section>
