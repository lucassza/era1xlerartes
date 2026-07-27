@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $showForm = $user !== null && ! $user->isBanned();
    $status = session('engagement_status');
@endphp

<section aria-labelledby="post-comment-form-title" class="overflow-hidden rounded-card border border-line bg-surface-raised shadow-card">
    <div class="bg-gradient-to-r from-primary/[0.06] to-secondary/[0.04] px-5 py-4">
        <h2 id="post-comment-form-title" class="font-display text-xl font-bold text-foreground">Deixe seu comentário</h2>
        <p class="mt-1 text-sm text-muted">Sua opinião ajuda a construir a nossa comunidade. Comentários passam por moderação antes de aparecer.</p>
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
                para comentar.
            </p>
        @elseif (! $user->hasVerifiedEmail())
            <p class="rounded-control border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-foreground">
                Confirme seu e-mail para poder comentar.
                <a href="{{ route('verification.notice') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Reenviar e-mail de verificação</a>.
            </p>
        @elseif (! $showForm)
            <p class="rounded-control border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-foreground">
                Sua conta está bloqueada. Procure o administrador.
            </p>
        @else
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="space-y-4" novalidate>
                @csrf

                <div aria-hidden="true" class="hidden">
                    <label>
                        Não preencha este campo
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="{{ old('website') }}">
                    </label>
                </div>

                <div class="rounded-control bg-surface px-4 py-3 text-sm text-foreground">
                    Enviando como <strong>{{ $user->name }}</strong>
                </div>

                <x-forms.rating-picker name="score" :value="old('score', 5)" label="Sua nota" :error="$errors->first('score')" />

                <div>
                    <label for="post-comment-content" class="mb-2 block text-sm font-semibold text-foreground">Comentário</label>
                    <textarea
                        id="post-comment-content"
                        name="content"
                        rows="4"
                        required
                        minlength="5"
                        maxlength="800"
                        @error('content') aria-invalid="true" aria-describedby="post-comment-content-error" @enderror
                        class="block w-full rounded-control border border-line bg-surface-raised px-3 py-2 text-foreground placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent"
                        placeholder="Compartilhe sua opinião sobre este post..."
                    >{{ old('content') }}</textarea>
                    @error('content')
                        <p id="post-comment-content-error" class="mt-1 text-sm font-medium text-danger">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-xs text-muted">Mínimo 5 caracteres. Seu comentário será moderado antes de ser publicado.</p>
                    @enderror
                </div>

                <x-ui.button type="submit" class="w-full">Enviar comentário</x-ui.button>
            </form>
        @endif
    </div>
</section>
