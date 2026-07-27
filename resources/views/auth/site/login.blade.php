@php
    $status = session('site_auth_status');
@endphp

<x-layouts.app :meta="$meta ?? null">
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <div class="rounded-card border border-line bg-surface-raised p-6 shadow-card sm:p-8">
            <header class="mb-6 text-center">
                <h1 class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Entrar na sua conta</h1>
                <p class="mt-2 text-sm text-muted">Use seu e-mail e senha cadastrados para comentar e enviar depoimentos.</p>
            </header>

            @if ($status)
                <div role="status" aria-live="polite" class="mb-5 rounded-control border border-success/30 bg-success/10 px-4 py-3 text-sm text-foreground">
                    {{ $status }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-control border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-foreground">
                    <p class="font-semibold">Não foi possível entrar:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('site.login.store') }}" class="space-y-4" novalidate>
                @csrf

                <div aria-hidden="true" class="hidden">
                    <label>
                        Não preencha este campo
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="{{ old('website') }}">
                    </label>
                </div>

                <x-ui.input id="site-login-email" name="email" type="email" label="E-mail" :value="old('email')" :error="$errors->first('email')" autocomplete="email" required />
                <x-ui.input id="site-login-password" name="password" type="password" label="Senha" :error="$errors->first('password')" autocomplete="current-password" required />

                <label class="flex items-center gap-2 text-sm text-muted">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-line text-primary focus:ring-accent">
                    Manter conectado
                </label>

                <x-ui.button type="submit" class="w-full">Entrar</x-ui.button>
            </form>

            <p class="mt-6 text-center text-sm text-muted">
                Ainda não tem conta?
                <a href="{{ route('site.register') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Cadastre-se</a>
            </p>
        </div>
    </div>
</x-layouts.app>
