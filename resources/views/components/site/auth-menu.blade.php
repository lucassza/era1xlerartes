@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $avatarUrl = $user?->getFirstMediaUrl('avatar', 'thumb-300') ?: $user?->getFirstMediaUrl('avatar');
@endphp

@if ($user === null)
    <div class="hidden items-center gap-2 sm:flex">
        <a
            href="{{ route('site.login') }}"
            class="rounded-control px-3 py-2 text-sm font-semibold text-muted transition hover:bg-surface hover:text-foreground"
        >
            Entrar
        </a>
        <a
            href="{{ route('site.register') }}"
            class="rounded-control bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-strong"
        >
            Cadastre-se
        </a>
    </div>
    <div class="flex items-center gap-2 sm:hidden">
        <a
            href="{{ route('site.login') }}"
            class="rounded-control px-2.5 py-2 text-sm font-semibold text-foreground"
            aria-label="Entrar"
        >
            Entrar
        </a>
    </div>
@else
    <details class="relative hidden sm:block" data-site-auth-menu>
        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-control px-3 py-2 text-sm font-semibold text-foreground transition hover:bg-surface">
            @if (filled($avatarUrl))
                <img src="{{ $avatarUrl }}" alt="" width="28" height="28" class="size-7 rounded-full object-cover">
            @else
                <span aria-hidden="true" class="inline-flex size-7 items-center justify-center rounded-full bg-primary/10 text-xs font-bold uppercase text-primary">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </span>
            @endif
            <span class="max-w-[8rem] truncate">{{ $user->name }}</span>
            <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4 text-muted">
                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </summary>
        <div class="absolute right-0 mt-2 w-56 origin-top-right rounded-card border border-line bg-surface-raised p-2 shadow-floating focus:outline-none">
            <div class="px-3 py-2 text-xs text-muted">
                Logado como<br>
                <strong class="text-foreground">{{ $user->name }}</strong>
                @if ($user->isAdmin())
                    <span class="ml-1 inline-flex items-center rounded-pill bg-primary/10 px-2 py-0.5 text-[0.65rem] font-semibold uppercase text-primary">Admin</span>
                @endif
            </div>
            @if ($user->isAdmin())
                <a href="{{ url('/admin') }}" class="block rounded-control px-3 py-2 text-sm text-foreground hover:bg-surface">Painel admin</a>
            @endif
            <a href="{{ route('site.profile.edit') }}" class="block rounded-control px-3 py-2 text-sm text-foreground hover:bg-surface">Meu perfil</a>
            <a href="{{ route('testimonials.index') }}" class="block rounded-control px-3 py-2 text-sm text-foreground hover:bg-surface">Depoimentos</a>
            <form method="POST" action="{{ route('site.logout') }}">
                @csrf
                <button type="submit" class="block w-full rounded-control px-3 py-2 text-left text-sm text-foreground hover:bg-surface">Sair</button>
            </form>
        </div>
    </details>
    <div class="flex items-center gap-1 sm:hidden">
        <a
            href="{{ route('site.profile.edit') }}"
            class="rounded-control px-2.5 py-2 text-sm font-semibold text-foreground"
            aria-label="Minha conta"
        >
            Conta
        </a>
    </div>
@endif
