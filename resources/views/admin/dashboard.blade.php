<x-layouts.app :title="'Painel administrativo'">
    <main class="mx-auto min-h-screen max-w-5xl px-6 py-16">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-muted">Autenticado como</p>
                <h1 class="text-3xl font-semibold tracking-tight">{{ auth()->user()->name }}</h1>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-line px-4 py-2 text-sm font-medium transition hover:bg-surface">
                    Sair
                </button>
            </form>
        </div>
    </main>
</x-layouts.app>
