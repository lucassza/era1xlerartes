<nav aria-label="Categorias" {{ $attributes->class(['border-b border-line bg-surface']) }}>
    <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 py-2 sm:px-6 lg:px-8">
        @foreach ($navigationCategories as $category)
            <a href="{{ route('home') }}#{{ $category->slug }}" class="shrink-0 rounded-control px-3 py-2 text-sm font-semibold text-muted transition duration-200 ease-standard hover:bg-surface-raised hover:text-primary">
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</nav>
