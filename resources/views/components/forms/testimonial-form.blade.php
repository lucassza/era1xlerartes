@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $showForm = $user !== null && ! $user->isBanned();
    $status = session('engagement_status');
@endphp

<section aria-labelledby="testimonial-form-title" class="overflow-hidden rounded-card border border-line bg-surface-raised shadow-card">
    <div class="bg-gradient-to-r from-primary/[0.06] to-secondary/[0.04] px-5 py-4">
        <h2 id="testimonial-form-title" class="font-display text-xl font-bold text-foreground">Compartilhe seu depoimento</h2>
        <p class="mt-1 text-sm text-muted">Conte como o blog fez parte da sua história. Seu depoimento será revisado antes de ser publicado.</p>
    </div>

    <div class="space-y-4 p-5">
        @if ($status)
            <div role="status" aria-live="polite" class="rounded-control border border-success/30 bg-success/10 px-4 py-3 text-sm text-foreground">
                {{ $status }}
            </div>
        @endif

        @if (! $user)
            <p class="rounded-control bg-surface px-4 py-3 text-sm text-muted">
                Você precisa
                <a href="{{ route('site.login') }}" class="font-semibold text-primary underline-offset-4 hover:underline">entrar</a>
                ou
                <a href="{{ route('site.register') }}" class="font-semibold text-primary underline-offset-4 hover:underline">criar uma conta</a>
                para enviar um depoimento.
            </p>
        @elseif (! $user->hasVerifiedEmail())
            <p class="rounded-control border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-foreground">
                Confirme seu e-mail para poder enviar depoimentos.
                <a href="{{ route('verification.notice') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Reenviar e-mail de verificação</a>.
            </p>
        @elseif (! $showForm)
            <p class="rounded-control border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-foreground">
                Sua conta está bloqueada. Procure o administrador.
            </p>
        @else
            <form method="POST" action="{{ route('testimonials.store') }}" class="space-y-4" novalidate>
                @csrf

                <div aria-hidden="true" class="hidden">
                    <label>
                        Não preencha este campo
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="{{ old('website') }}">
                    </label>
                </div>

                <div class="rounded-control bg-surface px-4 py-3 text-sm text-foreground">
                    Enviando como <strong>{{ $user->name }}</strong>
                    @if (filled($user->city))
                        · {{ $user->city }}
                    @endif
                </div>

                <x-forms.rating-picker name="score" :value="old('score', 5)" label="Sua nota" :error="$errors->first('score')" />

                <x-ui.input id="testimonial-city" name="city" type="text" label="Cidade (opcional)" :value="old('city', $user->city)" :error="$errors->first('city')" maxlength="120" autocomplete="address-level2" />

                <div>
                    <label for="testimonial-content" class="mb-2 block text-sm font-semibold text-foreground">Seu depoimento</label>
                    <textarea
                        id="testimonial-content"
                        name="content"
                        rows="5"
                        required
                        minlength="20"
                        maxlength="1000"
                        @error('content') aria-invalid="true" aria-describedby="testimonial-content-error" @enderror
                        class="block w-full rounded-control border border-line bg-surface-raised px-3 py-2 text-foreground placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-secondary"
                        placeholder="Conte como foi sua experiência com o blog..."
                    >{{ old('content') }}</textarea>
                    @error('content')
                        <p id="testimonial-content-error" class="mt-1 text-sm font-medium text-danger">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-xs text-muted">Mínimo 20 caracteres. Seu depoimento será moderado antes de ser publicado.</p>
                    @enderror
                </div>

                <x-ui.button type="submit" class="w-full">Enviar depoimento</x-ui.button>
            </form>
        @endif
    </div>
</section>
