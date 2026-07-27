@props(['paginator'])

@if ($paginator->hasPages())
    <nav aria-label="Paginação de posts" class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-line pt-8 sm:flex-row">
        <p class="text-sm text-muted">
            Mostrando <strong class="font-semibold text-foreground">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
            de <strong class="font-semibold text-foreground">{{ $paginator->total() }}</strong> posts
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex size-10 cursor-not-allowed items-center justify-center rounded-control text-muted" aria-disabled="true" aria-label="Página anterior">
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M14 8a.75.75 0 0 0-.75-.75H4.56l3.22-3.22a.75.75 0 0 0-1.06-1.06l-4.5 4.5a.75.75 0 0 0 0 1.06l4.5 4.5a.75.75 0 0 0 1.06-1.06L4.56 8.75H13.25A.75.75 0 0 0 14 8Z" clip-rule="evenodd" /></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex size-10 items-center justify-center rounded-control text-foreground transition duration-200 ease-standard hover:bg-surface" aria-label="Página anterior">
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M14 8a.75.75 0 0 0-.75-.75H4.56l3.22-3.22a.75.75 0 0 0-1.06-1.06l-4.5 4.5a.75.75 0 0 0 0 1.06l4.5 4.5a.75.75 0 0 0 1.06-1.06L4.56 8.75H13.25A.75.75 0 0 0 14 8Z" clip-rule="evenodd" /></svg>
                </a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page === $paginator->currentPage())
                    <span class="inline-flex size-10 items-center justify-center rounded-control bg-primary text-sm font-semibold text-white" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="inline-flex size-10 items-center justify-center rounded-control text-sm font-semibold text-foreground transition duration-200 ease-standard hover:bg-surface">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex size-10 items-center justify-center rounded-control text-foreground transition duration-200 ease-standard hover:bg-surface" aria-label="Próxima página">
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06L8.97 8.75H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd" /></svg>
                </a>
            @else
                <span class="inline-flex size-10 cursor-not-allowed items-center justify-center rounded-control text-muted" aria-disabled="true" aria-label="Próxima página">
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06L8.97 8.75H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
