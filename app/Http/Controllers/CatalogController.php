<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CatalogCacheService;
use App\Services\SeoService;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly CatalogCacheService $catalogCache,
        private readonly SeoService $seo,
    ) {}

    public function __invoke(): View
    {
        return $this->index();
    }

    public function index(): View
    {
        $settings = $this->catalogCache->settings();
        $showUnavailable = (bool) $settings->getAttribute('show_unavailable');

        $featured = Product::query()
            ->publiclyVisible($showUnavailable)
            ->featured()
            ->with(['category', 'brand'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        $latest = Product::query()
            ->publiclyVisible($showUnavailable)
            ->with(['category', 'brand'])
            ->latest('published_at')
            ->limit(12)
            ->get();

        $seo = $this->seo->forCatalogHome($settings);

        return view('catalog.home', [
            'settings' => $settings,
            'featured' => $featured,
            'latest' => $latest,
            'categories' => $this->catalogCache->activeCategories(),
            'meta' => $seo['meta'],
            'jsonLd' => $seo['jsonLd'],
        ]);
    }
}
