<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CatalogCategory;
use App\Models\Product;
use App\Services\CatalogCacheService;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogCategoryController extends Controller
{
    public function __construct(
        private readonly CatalogCacheService $catalogCache,
        private readonly SeoService $seo,
    ) {}

    public function show(Request $request, string $slug): View
    {
        $category = $this->catalogCache
            ->allActiveCategories()
            ->firstWhere('slug', $slug);

        abort_unless($category instanceof CatalogCategory, 404);

        $settings = $this->catalogCache->settings();
        $showUnavailable = (bool) $settings->getAttribute('show_unavailable');
        $perPage = max(1, (int) ($settings->getAttribute('per_page') ?: 12));

        $sort = (string) $request->query('ordenar', 'recent');
        if (! in_array($sort, ['recent', 'name_asc', 'name_desc', 'price_asc', 'price_desc', 'featured', 'sort_order'], true)) {
            $sort = 'recent';
        }

        $products = Product::query()
            ->publiclyVisible($showUnavailable)
            ->with(['category', 'brand'])
            ->inCategories([$category])
            ->ordered($sort)
            ->paginate($perPage)
            ->withQueryString();

        $subcategories = $category->children()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $seo = $this->seo->forCatalogCategory($category, $settings);

        return view('catalog.category', [
            'category' => $category,
            'subcategories' => $subcategories,
            'products' => $products,
            'settings' => $settings,
            'sort' => $sort,
            'meta' => $seo['meta'],
            'jsonLd' => $seo['jsonLd'],
        ]);
    }
}
