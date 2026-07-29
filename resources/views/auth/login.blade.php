<x-layouts.app :title="'Entrar'">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-6 py-16">
        <section class="w-full rounded-2xl border border-line bg-white p-8 shadow-sm">
            <div class="mb-8 space-y-2">
                <h1 class="text-2xl font-semibold tracking-tight">Acesso administrativo</h1>
                <p class="text-sm text-muted">
                    Entre com seu e-mail e senha para acessar o painel do blog.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger">
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
                        class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-secondary focus:ring-2 focus:ring-secondary"
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
                        class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-secondary focus:ring-2 focus:ring-secondary"
                    >
                </div>

                <label class="flex items-center gap-3 text-sm text-muted">
                    <input type="checkbox" name="remember" value="1" class="rounded border-line text-primary focus:ring-secondary">
                    <span>Lembrar acesso</span>
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-medium text-white transition hover:bg-primary-strong">
                    Entrar
                </button>
            </form>
        </section>
    </main>
</x-layouts.app>
