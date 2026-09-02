@php
    /**
     * Formulário de filtros do catálogo.
     *
     * Usado duas vezes: no trilho fixo do desktop e dentro da folha inferior do
     * mobile. `$scope` prefixa os ids dos campos para que as duas instâncias não
     * colidam. Os parâmetros enviados são os mesmos de sempre: categoria, marca,
     * disponibilidade, preco_min, preco_max e destaque.
     */
    $scope = $scope ?? 'desktop';
    $activeCategory = $activeFilters['categoria'] ?? '';
    $activeBrand = $activeFilters['marca'] ?? '';
    $activeAvailability = $activeFilters['disponibilidade'] ?? '';
    $brands = $filters['brands'] ?? collect();
@endphp

<form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-5">
    @if (! empty($activeFilters['buscar']))
        <input type="hidden" name="buscar" value="{{ $activeFilters['buscar'] }}">
    @endif
    <input type="hidden" name="ordenar" value="{{ $sort }}">

    <fieldset class="flex flex-col gap-2 border-b border-line pb-5">
        <legend class="mb-2 text-xs font-bold uppercase tracking-wider text-foreground">Categoria</legend>

        <label class="flex min-h-10 cursor-pointer items-center gap-2.5 rounded-control px-3 text-sm text-foreground transition duration-200 ease-standard hover:bg-surface has-checked:bg-primary/5 has-checked:font-bold has-checked:text-primary">
            <input type="radio" name="categoria" value="" @checked($activeCategory === '') class="size-4 border-line text-primary focus:ring-secondary">
            Todas as categorias
        </label>

        @foreach ($filters['categories'] ?? [] as $cat)
            <label class="flex min-h-10 cursor-pointer items-center gap-2.5 rounded-control px-3 text-sm text-foreground transition duration-200 ease-standard hover:bg-surface has-checked:bg-primary/5 has-checked:font-bold has-checked:text-primary">
                <input type="radio" name="categoria" value="{{ $cat->slug }}" @checked($activeCategory === $cat->slug) class="size-4 border-line text-primary focus:ring-secondary">
                {{ $cat->name }}
            </label>
        @endforeach
    </fieldset>

    @if ($brands->count() > 0)
        <fieldset class="flex flex-col gap-2 border-b border-line pb-5">
            <legend class="mb-2 text-xs font-bold uppercase tracking-wider text-foreground">Marca</legend>

            <div class="flex flex-wrap gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="marca" value="" @checked($activeBrand === '') class="peer sr-only">
                    <span class="inline-flex min-h-9 items-center rounded-pill border border-line bg-surface px-3.5 text-sm font-semibold text-foreground transition duration-200 ease-standard peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-secondary">
                        Todas
                    </span>
                </label>

                @foreach ($brands as $brand)
                    <label class="cursor-pointer">
                        <input type="radio" name="marca" value="{{ $brand->slug }}" @checked($activeBrand === $brand->slug) class="peer sr-only">
                        <span class="inline-flex min-h-9 items-center rounded-pill border border-line bg-surface px-3.5 text-sm font-semibold text-foreground transition duration-200 ease-standard peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-secondary">
                            {{ $brand->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    <fieldset class="flex flex-col gap-2 border-b border-line pb-5">
        <legend class="mb-2 text-xs font-bold uppercase tracking-wider text-foreground">Disponibilidade</legend>

        <label class="flex min-h-10 cursor-pointer items-center gap-2.5 rounded-control px-3 text-sm text-foreground transition duration-200 ease-standard hover:bg-surface has-checked:bg-primary/5 has-checked:font-bold has-checked:text-primary">
            <input type="radio" name="disponibilidade" value="" @checked($activeAvailability === '') class="size-4 border-line text-primary focus:ring-secondary">
            Todas
        </label>

        @foreach (\App\Enums\ProductAvailability::cases() as $availability)
            <label class="flex min-h-10 cursor-pointer items-center gap-2.5 rounded-control px-3 text-sm text-foreground transition duration-200 ease-standard hover:bg-surface has-checked:bg-primary/5 has-checked:font-bold has-checked:text-primary">
                <input type="radio" name="disponibilidade" value="{{ $availability->value }}" @checked($activeAvailability === $availability->value) class="size-4 border-line text-primary focus:ring-secondary">
                {{ $availability->label() }}
            </label>
        @endforeach
    </fieldset>

    @if (! empty($filters['show_price_filter']))
        <fieldset class="flex flex-col gap-3 border-b border-line pb-5">
            <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-foreground">Faixa de preço</legend>

            <div class="flex items-center gap-2">
                <label for="{{ $scope }}-preco-min" class="sr-only">Preço mínimo</label>
                <input
                    id="{{ $scope }}-preco-min"
                    type="number" name="preco_min" step="0.01" min="0" inputmode="decimal"
                    value="{{ $activeFilters['preco_min'] ?? '' }}" placeholder="Mín."
                    class="h-11 w-full rounded-control border border-line bg-surface px-3 text-sm text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-secondary"
                >
                <span class="text-muted" aria-hidden="true">–</span>
                <label for="{{ $scope }}-preco-max" class="sr-only">Preço máximo</label>
                <input
                    id="{{ $scope }}-preco-max"
                    type="number" name="preco_max" step="0.01" min="0" inputmode="decimal"
                    value="{{ $activeFilters['preco_max'] ?? '' }}" placeholder="Máx."
                    class="h-11 w-full rounded-control border border-line bg-surface px-3 text-sm text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-secondary"
                >
            </div>
        </fieldset>
    @endif

    <label class="flex min-h-11 cursor-pointer items-center justify-between gap-3 rounded-control border border-line px-3.5 text-sm font-semibold text-foreground transition duration-200 ease-standard has-checked:border-primary/20 has-checked:bg-secondary/10 has-checked:text-primary">
        Apenas destaques
        <input type="checkbox" name="destaque" value="1" @checked(! empty($activeFilters['destaque'])) class="size-4 rounded border-line text-primary focus:ring-secondary">
    </label>

    <div class="flex flex-col gap-2 pt-1">
        <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-control bg-primary px-4 text-sm font-bold text-white transition duration-200 ease-standard hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
            Aplicar filtros
        </button>
        <a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-surface-raised px-4 text-sm font-semibold text-foreground transition duration-200 ease-standard hover:bg-surface focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary">
            Limpar
        </a>
    </div>
</form>
