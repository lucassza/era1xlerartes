<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BlogSetting;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Observers\CatalogObserver;
use App\Services\CatalogCacheService;
use App\Services\PublicNavigationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('components.site.header', function ($view): void {
            $navigation = app(PublicNavigationService::class);
            $catalog = app(CatalogCacheService::class);

            $view->with([
                'navigationCategories' => $navigation->categories(),
                'navigationCatalogCategories' => $navigation->catalogTopCategories(),
                'navigationCatalogBrands' => $navigation->activeBrands(),
                'catalogSettings' => $catalog->settings(),
            ]);
        });

        View::composer('components.site.category-nav', function ($view): void {
            $view->with([
                'navigationCategories' => app(PublicNavigationService::class)->categories(),
            ]);
        });

        View::composer('components.site.footer', function ($view): void {
            $catalog = app(CatalogCacheService::class);
            $navigation = app(PublicNavigationService::class);

            $view->with([
                'footerCatalogCategories' => $navigation->catalogTopCategories(6),
                'footerCatalogSettings' => $catalog->settings(),
                'footerSettings' => BlogSetting::query()->first(),
            ]);
        });

        $observer = app(CatalogObserver::class);
        CatalogCategory::observe($observer);
        Brand::observe($observer);
        CatalogSetting::observe($observer);
    }
}
