<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use Illuminate\Support\Collection;

class CatalogCacheService
{
    public const TTL = 600;

    public const KEY_TREE = 'catalog:categories:tree';

    public const KEY_BRANDS = 'catalog:brands:active';

    public const KEY_SETTINGS = 'catalog:settings:current';

    public function activeCategories(): Collection
    {
        if ($this->shouldBypassCache()) {
            return CatalogCategory::query()
                ->where('is_active', true)
                ->with('children')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->whereNull('parent_id')
                ->values();
        }

        return cache()->remember(
            self::KEY_TREE,
            self::TTL,
            fn () => CatalogCategory::query()
                ->where('is_active', true)
                ->with('children')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->whereNull('parent_id')
                ->values(),
        );
    }

    public function allActiveCategories(): Collection
    {
        if ($this->shouldBypassCache()) {
            return CatalogCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return cache()->remember(
            'catalog:categories:flat',
            self::TTL,
            fn () => CatalogCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        );
    }

    public function activeBrands(): Collection
    {
        if ($this->shouldBypassCache()) {
            return Brand::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return cache()->remember(
            self::KEY_BRANDS,
            self::TTL,
            fn () => Brand::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        );
    }

    public function settings(): CatalogSetting
    {
        if ($this->shouldBypassCache()) {
            return CatalogSetting::current();
        }

        $key = self::KEY_SETTINGS;
        $cached = cache()->get($key);
        if ($cached instanceof CatalogSetting) {
            return $cached;
        }
        $settings = CatalogSetting::current();
        cache()->put($key, $settings, self::TTL);

        return $settings;
    }

    public function invalidateCategories(): void
    {
        cache()->forget(self::KEY_TREE);
        cache()->forget('catalog:categories:flat');
    }

    public function invalidateBrands(): void
    {
        cache()->forget(self::KEY_BRANDS);
    }

    public function invalidateSettings(): void
    {
        cache()->forget(self::KEY_SETTINGS);
    }

    public function invalidateAll(): void
    {
        $this->invalidateCategories();
        $this->invalidateBrands();
        $this->invalidateSettings();
    }

    private function shouldBypassCache(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        if (($_ENV['APP_ENV'] ?? null) === 'testing') {
            return true;
        }

        return config('database.default') === 'pgsql_testing';
    }
}
