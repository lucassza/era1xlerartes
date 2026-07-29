<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Services\CatalogCacheService;

class CatalogObserver
{
    public function __construct(private readonly CatalogCacheService $cache) {}

    public function saved(CatalogCategory|CatalogSetting|Brand $model): void
    {
        if ($model instanceof CatalogCategory) {
            $this->cache->invalidateCategories();

            return;
        }

        if ($model instanceof Brand) {
            $this->cache->invalidateBrands();

            return;
        }

        $this->cache->invalidateSettings();
    }

    public function deleted(CatalogCategory|CatalogSetting|Brand $model): void
    {
        $this->saved($model);
    }
}
