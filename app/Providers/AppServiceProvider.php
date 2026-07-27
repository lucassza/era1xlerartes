<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Observers\CatalogObserver;
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

        View::composer(['components.site.header', 'components.site.category-nav'], function ($view): void {
            $navigation = app(PublicNavigationService::class);

            $view->with([
                'navigationCategories' => $navigation->categories(),
            ]);
        });

        $observer = app(CatalogObserver::class);
        CatalogCategory::observe($observer);
        Brand::observe($observer);
        CatalogSetting::observe($observer);
    }
}
