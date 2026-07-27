@props([
    'post',
    'comments' => null,
])

@php
    /** @var \App\Models\Post $post */
    $comments ??= $post->approvedComments()->latest('approved_at')->get();
    $showForm = $post->commentsEnabled();
@endphp

<section aria-labelledby="post-comments-title" id="comentarios" class="space-y-6 scroll-mt-6">
    <header class="flex items-center justify-between gap-3 border-b border-line pb-3">
        <h2 id="post-comments-title" class="font-display text-2xl font-bold text-foreground">
            Comentários
            <span class="ml-1 text-base font-semibold text-muted">({{ $comments->count() }})</span>
        </h2>
    </header>

    @if (! $showForm)
        <p class="rounded-control border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-foreground">
            Os comentários estão temporariamente desabilitados para este post.
        </p>
    @else
        <x-forms.post-comment-form :post="$post" />
    @endif

    @if ($comments->isEmpty())
        <x-ui.empty-state title="Ainda sem comentários" description="Seja o primeiro a compartilhar sua opinião!" />
    @else
        <ol class="space-y-4">
            @foreach ($comments as $comment)
                <li class="rounded-card border border-line bg-surface-raised p-5 shadow-card">
                    <header class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-display text-base font-bold text-foreground">{{ $comment->name }}</p>
                            <p class="text-xs text-muted">
                                @if ($comment->approved_at)
                                    {{ $comment->approved_at->format('d/m/Y H:i') }}
                                @endif
                                @if ($comment->city)
                                    · {{ $comment->city }}
                                @endif
                            </p>
                        </div>
                        <div class="text-sm text-warning" aria-label="Nota {{ $comment->score }} de 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <span aria-hidden="true">{{ $i <= $comment->score ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                    </header>
                    <div class="prose prose-sm mt-3 max-w-none text-foreground [&_a]:text-primary [&_a]:underline">
                        {!! nl2br(e($comment->content)) !!}
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
