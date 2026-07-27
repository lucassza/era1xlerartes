@props([
    'lines' => 1,
    'height' => 'h-4',
])

<div role="status" aria-busy="true" aria-label="Carregando conteúdo" {{ $attributes->class(['space-y-3']) }}>
    @for ($line = 0; $line < $lines; $line++)
        <div @class(['rounded-control bg-surface', $height, 'w-3/4' => $line === $lines - 1 && $lines > 1])></div>
    @endfor
</div>
