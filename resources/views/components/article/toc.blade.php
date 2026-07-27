@props(['headings' => [], 'desktop' => false])

@if (count($headings))
    <details @if ($desktop) open @endif class="rounded-card border border-line bg-surface p-4">
        <summary class="cursor-pointer font-display font-semibold text-foreground">Neste artigo</summary>
        <ol class="mt-4 space-y-2 border-t border-line pt-4 text-sm">
            @foreach ($headings as $heading)
                <li @class(['pl-3' => $heading['level'] === 3])>
                    <a href="#{{ $heading['id'] }}" class="rounded text-muted underline-offset-4 hover:text-primary hover:underline">{{ $heading['text'] }}</a>
                </li>
            @endforeach
        </ol>
    </details>
@endif
