@props(['post'])

@php
    $postUrl = route('posts.show', $post->slug);
    $message = $post->title;
@endphp

<section aria-labelledby="share-heading" class="mt-10 border-t border-line pt-6">
    <h2 id="share-heading" class="font-display text-lg font-semibold text-foreground">Compartilhar</h2>
    <ul class="mt-3 flex flex-wrap gap-3 text-sm font-semibold">
        <li><a href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($postUrl) }}" target="_blank" rel="noopener noreferrer" class="rounded-control border border-line bg-surface-raised px-4 py-2 text-foreground hover:bg-surface">LinkedIn</a></li>
        <li><a href="https://twitter.com/intent/tweet?{{ http_build_query(['text' => $message, 'url' => $postUrl]) }}" target="_blank" rel="noopener noreferrer" class="rounded-control border border-line bg-surface-raised px-4 py-2 text-foreground hover:bg-surface">X (Twitter)</a></li>
        <li><a href="mailto:?{{ http_build_query(['subject' => $message, 'body' => $postUrl]) }}" class="rounded-control border border-line bg-surface-raised px-4 py-2 text-foreground hover:bg-surface">E-mail</a></li>
    </ul>
</section>
