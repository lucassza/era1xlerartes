<x-layouts.app :meta="$meta ?? null">
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <div class="rounded-card border border-line bg-surface-raised p-6 shadow-card sm:p-8">
            <header class="mb-6 text-center">
                <h1 class="font-display text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Criar conta</h1>
                <p class="mt-2 text-sm text-muted">Cadastre-se para comentar nos posts e enviar depoimentos.</p>
            </header>

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-control border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-foreground">
                    <p class="font-semibold">Não foi possível criar sua conta:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('site.register.store') }}" class="space-y-4" novalidate>
                @csrf

                <div aria-hidden="true" class="hidden">
                    <label>
                        Não preencha este campo
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="{{ old('website') }}">
                    </label>
                </div>

                <x-ui.input id="site-register-name" name="name" type="text" label="Nome" :value="old('name')" :error="$errors->first('name')" autocomplete="name" required />
                <x-ui.input id="site-register-email" name="email" type="email" label="E-mail" :value="old('email')" :error="$errors->first('email')" autocomplete="email" required />
                <x-ui.input id="site-register-city" name="city" type="text" label="Cidade (opcional)" :value="old('city')" :error="$errors->first('city')" autocomplete="address-level2" />
                <x-ui.input id="site-register-password" name="password" type="password" label="Senha (mínimo 8 caracteres)" :error="$errors->first('password')" autocomplete="new-password" required />
                <x-ui.input id="site-register-password-confirmation" name="password_confirmation" type="password" label="Confirme a senha" :error="$errors->first('password_confirmation')" autocomplete="new-password" required />

                <label class="flex items-start gap-2 text-sm text-muted">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-1 size-4 rounded border-line text-primary focus:ring-secondary">
                    <span>Li e aceito os termos de uso e a política de privacidade.</span>
                </label>
                @error('terms')
                    <p class="text-xs text-danger" role="alert">{{ $message }}</p>
                @enderror

                <x-ui.button type="submit" class="w-full">Criar conta</x-ui.button>
            </form>

            <p class="mt-6 text-center text-sm text-muted">
                Já tem conta?
                <a href="{{ route('site.login') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Entrar</a>
            </p>
        </div>
    </div>
</x-layouts.app>
