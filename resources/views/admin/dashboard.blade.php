<x-layouts.app :title="'Painel administrativo'">
    <main class="mx-auto min-h-screen max-w-5xl px-6 py-16">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Autenticado como</p>
                <h1 class="text-3xl font-semibold tracking-tight">{{ auth()->user()->name }}</h1>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">
                    Sair
                </button>
            </form>
        </div>
    </main>
</x-layouts.app>
