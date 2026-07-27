@php
    $status = session('status');
@endphp

<x-layouts.app :meta="$meta ?? null">
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <div class="rounded-card border border-line bg-surface-raised p-6 shadow-card sm:p-8">
            <header class="mb-6 text-center">
                <h1 class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Verifique seu e-mail</h1>
                <p class="mt-2 text-sm text-muted">Enviamos um link de confirmação para o seu e-mail. Clique no link para liberar comentários e depoimentos.</p>
            </header>

            @if ($status)
                <div role="status" aria-live="polite" class="mb-5 rounded-control border border-success/30 bg-success/10 px-4 py-3 text-sm text-foreground">
                    {{ $status }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                @csrf
                <x-ui.button type="submit" class="w-full">Reenviar e-mail de verificação</x-ui.button>
            </form>

            <form method="POST" action="{{ route('site.logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-control border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-foreground hover:bg-surface-raised">Sair</button>
            </form>
        </div>
    </div>
</x-layouts.app>
