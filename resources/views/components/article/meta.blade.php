@props(['post', 'readingTime'])

<div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-muted">
    @if ($post->category)
        <a href="{{ route('categories.show', $post->category->slug) }}" class="rounded font-semibold text-primary underline-offset-4 hover:text-secondary hover:underline">{{ $post->category->name }}</a>
    @endif
    @if ($post->author)
        <span>Por <a href="{{ route('authors.show', $post->author->slug) }}" class="rounded font-semibold text-foreground underline-offset-4 hover:text-primary hover:underline">{{ $post->author->name }}</a></span>
    @endif
    @if ($post->published_at)
        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d \d\e F \d\e Y') }}</time>
    @endif
    <span>{{ $readingTime }} min de leitura</span>
</div>
