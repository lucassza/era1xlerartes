@props(['post'])

<nav aria-label="Trilha de navegação" class="mb-6 text-sm text-muted sm:mb-8">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <li><a href="{{ route('home') }}" class="rounded text-foreground underline-offset-4 hover:text-primary hover:underline">Início</a></li>
        <li aria-hidden="true">/</li>
        @if ($post->category)
            <li><a href="{{ route('categories.show', $post->category->slug) }}" class="rounded underline-offset-4 hover:text-primary hover:underline">{{ $post->category->name }}</a></li>
            <li aria-hidden="true">/</li>
        @endif
        <li aria-current="page" class="max-w-56 truncate text-foreground">{{ $post->title }}</li>
    </ol>
</nav>
