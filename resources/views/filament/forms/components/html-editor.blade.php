@php
    $statePath = $getStatePath();
    $bundle = public_path('js/hugerte/hugerte-editor.js');
    $bundleUrl = asset('js/hugerte/hugerte-editor.js').'?v='.(is_file($bundle) ? filemtime($bundle) : 0);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-load
        x-load-src="{{ $bundleUrl }}"
        x-data="hugerteEditor({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            height: @js($getEditorHeight()),
            readOnly: @js($isDisabled()),
        })"
        class="w-full"
    >
        <textarea x-ref="editor" class="hidden"></textarea>
    </div>
</x-dynamic-component>
