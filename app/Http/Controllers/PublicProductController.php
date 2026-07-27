<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CatalogCacheService;
use App\Services\SeoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProductController extends Controller
{
    public function __construct(
        private readonly CatalogCacheService $catalogCache,
        private readonly SeoService $seo,
    ) {}

    public function index(Request $request): View
    {
        $settings = $this->catalogCache->settings();
        $showUnavailable = (bool) $settings->getAttribute('show_unavailable');
        $perPage = max(1, (int) ($settings->getAttribute('per_page') ?: 12));

        $sort = (string) $request->query('ordenar', 'recent');
        if (! in_array($sort, ['recent', 'name_asc', 'name_desc', 'price_asc', 'price_desc', 'featured', 'sort_order'], true)) {
            $sort = 'recent';
        }

        $query = Product::query()
            ->publiclyVisible($showUnavailable)
            ->with(['category', 'brand']);

        $this->applyFilters($query, $request);

        $query->ordered($sort);

        $products = $query->paginate($perPage)->withQueryString();

        $filters = [
            'categories' => $this->catalogCache->allActiveCategories(),
            'brands' => $this->catalogCache->activeBrands(),
            'show_price_filter' => true,
        ];

        $seo = $this->seo->forCatalogIndex($request, $settings);

        return view('catalog.index', [
            'products' => $products,
            'settings' => $settings,
            'filters' => $filters,
            'sort' => $sort,
            'meta' => $seo['meta'],
            'jsonLd' => $seo['jsonLd'],
            'activeFilters' => $this->collectActiveFilters($request),
        ]);
    }

    public function show(string $slug): View
    {
        $settings = $this->catalogCache->settings();
        $showUnavailable = (bool) $settings->getAttribute('show_unavailable');

        $product = Product::query()
            ->with(['category.parent', 'brand', 'attributeValues.attribute.group', 'media', 'tags'])
            ->where('slug', $slug)
            ->publiclyVisible($showUnavailable)
            ->firstOrFail();

        $related = Product::query()
            ->with(['brand'])
            ->publiclyVisible($showUnavailable)
            ->where('id', '!=', $product->getKey())
            ->where(function ($q) use ($product): void {
                $q->where('catalog_category_id', $product->getAttribute('catalog_category_id'))
                    ->when($product->getAttribute('brand_id'), fn ($b) => $b->orWhere('brand_id', $product->getAttribute('brand_id')));
            })
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $seo = $this->seo->forProduct($product, $settings);

        return view('catalog.show', [
            'product' => $product,
            'related' => $related,
            'settings' => $settings,
            'meta' => $seo['meta'],
            'jsonLd' => $seo['jsonLd'],
        ]);
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        if ($categorySlug = $request->query('categoria')) {
            $category = $this->catalogCache->allActiveCategories()->firstWhere('slug', $categorySlug);
            if ($category) {
                $query->inCategories([$category]);
            }
        }

        if ($brandSlug = $request->query('marca')) {
            $brand = $this->catalogCache->activeBrands()->firstWhere('slug', $brandSlug);
            if ($brand) {
                $query->ofBrand($brand->getKey());
            }
        }

        if ($availability = $request->query('disponibilidade')) {
            $query->withAvailability((string) $availability);
        }

        if ($request->boolean('destaque')) {
            $query->where('is_featured', true);
        }

        if ($search = $request->query('buscar')) {
            $query->searchTerm((string) $search);
        }

        $min = $request->query('preco_min');
        $max = $request->query('preco_max');
        if (is_numeric($min) || is_numeric($max)) {
            $query->priceBetween(
                is_numeric($min) ? (float) $min : null,
                is_numeric($max) ? (float) $max : null,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function collectActiveFilters(Request $request): array
    {
        return [
            'categoria' => $request->query('categoria'),
            'marca' => $request->query('marca'),
            'disponibilidade' => $request->query('disponibilidade'),
            'destaque' => $request->boolean('destaque'),
            'buscar' => $request->query('buscar'),
            'preco_min' => $request->query('preco_min'),
            'preco_max' => $request->query('preco_max'),
        ];
    }
}
