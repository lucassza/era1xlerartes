@php
    $status = session('site_profile_status');
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
@endphp

<x-layouts.app :meta="$meta ?? null">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <header class="mb-8 max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Conta</p>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Meu perfil</h1>
            <p class="mt-3 text-base leading-7 text-muted">Gerencie seus dados, sua foto e a senha de acesso ao Era1x Ler Artes.</p>
        </header>

        @if ($status)
            <div role="status" aria-live="polite" class="mb-6 rounded-control border border-success/30 bg-success/10 px-4 py-3 text-sm text-foreground">
                {{ $status }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-control border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-foreground">
                <p class="font-semibold">Não foi possível atualizar seu perfil:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section aria-labelledby="profile-avatar-heading" class="rounded-card border border-line bg-surface-raised p-5 shadow-card sm:p-6">
                <header class="mb-5">
                    <h2 id="profile-avatar-heading" class="font-display text-xl font-bold text-foreground">Avatar</h2>
                    <p class="mt-1 text-sm leading-6 text-muted">Use uma imagem quadrada de pelo menos 200 por 200 pixels.</p>
                </header>

                <div class="mb-6 flex items-center gap-4">
                    @if (filled($avatarUrl))
                        <img src="{{ $avatarUrl }}" alt="Avatar de {{ $user->name }}" width="112" height="112" class="size-28 rounded-full object-cover ring-4 ring-primary/10">
                    @else
                        <div role="img" aria-label="Inicial de {{ $user->name }}" class="flex size-28 items-center justify-center rounded-full bg-primary/10 font-display text-4xl font-bold uppercase text-primary ring-4 ring-primary/5">{{ $initial }}</div>
                    @endif
                    <div>
                        <p class="font-semibold text-foreground">{{ $user->name }}</p>
                        <p id="profile-avatar-help" class="mt-1 text-sm text-muted">JPG, PNG ou WebP até 2 MB.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('site.profile.avatar.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div aria-hidden="true" class="hidden">
                        <label>
                            Não preencha este campo
                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="{{ old('website') }}">
                        </label>
                    </div>

                    <div class="space-y-2">
                        <label for="profile-avatar" class="block text-sm font-semibold text-foreground">Escolha uma imagem</label>
                        <input id="profile-avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-describedby="profile-avatar-help{{ $errors->has('avatar') ? ' profile-avatar-error' : '' }}" class="block min-h-11 w-full rounded-control border border-line bg-surface-raised px-3 py-2 text-sm text-foreground file:mr-3 file:rounded-control file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-semibold file:text-primary" @error('avatar') aria-invalid="true" @enderror required>
                        @error('avatar')
                            <p id="profile-avatar-error" class="text-sm font-medium text-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" class="w-full">Atualizar avatar</x-ui.button>
                </form>

                <form method="POST" action="{{ route('site.profile.avatar.destroy') }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="secondary" class="w-full">Remover avatar</x-ui.button>
                </form>
            </section>

            <section aria-labelledby="profile-data-heading" class="rounded-card border border-line bg-surface-raised p-5 shadow-card sm:p-6 lg:col-span-2">
                <header class="mb-5">
                    <h2 id="profile-data-heading" class="font-display text-xl font-bold text-foreground">Dados pessoais</h2>
                    <p class="mt-1 text-sm leading-6 text-muted">Mantenha suas informações de contato atualizadas.</p>
                </header>

                <form method="POST" action="{{ route('site.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div aria-hidden="true" class="hidden">
                        <label>
                            Não preencha este campo
                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="{{ old('website') }}">
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input id="profile-name" name="name" type="text" label="Nome" :value="old('name', $user->name)" :error="$errors->first('name')" autocomplete="name" required />
                        <x-ui.input id="profile-email" name="email" type="email" label="E-mail" :value="old('email', $user->email)" :error="$errors->first('email')" autocomplete="email" required />
                        <x-ui.input id="profile-city" name="city" type="text" label="Cidade (opcional)" :value="old('city', $user->city)" :error="$errors->first('city')" autocomplete="address-level2" />
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit">Salvar dados</x-ui.button>
                    </div>
                </form>
            </section>

            <section aria-labelledby="profile-password-heading" class="rounded-card border border-line bg-surface-raised p-5 shadow-card sm:p-6 lg:col-span-2">
                <header class="mb-5">
                    <h2 id="profile-password-heading" class="font-display text-xl font-bold text-foreground">Alterar senha</h2>
                    <p class="mt-1 text-sm leading-6 text-muted">Informe sua senha atual para criar uma nova senha de acesso.</p>
                </header>

                <form method="POST" action="{{ route('site.profile.password.update') }}" class="max-w-xl space-y-4">
                    @csrf
                    @method('PUT')

                    <x-ui.input id="profile-current-password" name="current_password" type="password" label="Senha atual" :error="$errors->first('current_password')" autocomplete="current-password" required />
                    <x-ui.input id="profile-password" name="password" type="password" label="Nova senha (mínimo 8 caracteres)" :error="$errors->first('password')" autocomplete="new-password" required />
                    <x-ui.input id="profile-password-confirmation" name="password_confirmation" type="password" label="Confirme a nova senha" :error="$errors->first('password_confirmation')" autocomplete="new-password" required />

                    <div class="flex justify-end">
                        <x-ui.button type="submit">Atualizar senha</x-ui.button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
