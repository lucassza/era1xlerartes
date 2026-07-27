<x-layouts.app :title="'Entrar'">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-6 py-16">
        <section class="w-full rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-8 space-y-2">
                <h1 class="text-2xl font-semibold tracking-tight">Acesso administrativo</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    Entre com seu e-mail e senha para acessar o painel do blog.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium">E-mail</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:border-zinc-500 dark:focus:ring-zinc-800"
                    >
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium">Senha</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:border-zinc-500 dark:focus:ring-zinc-800"
                    >
                </div>

                <label class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-300 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:ring-zinc-800">
                    <span>Lembrar acesso</span>
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-950 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                    Entrar
                </button>
            </form>
        </section>
    </main>
</x-layouts.app>
