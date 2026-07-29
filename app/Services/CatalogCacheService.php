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
            return $this->fetchActiveCategories();
        }

        $cached = $this->safeCacheGet(self::KEY_TREE);
        if ($cached instanceof Collection) {
            return $cached;
        }

        $fresh = $this->fetchActiveCategories();
        $this->safeCachePut(self::KEY_TREE, $fresh);

        return $fresh;
    }

    private function fetchActiveCategories(): Collection
    {
        return CatalogCategory::query()
            ->where('is_active', true)
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->whereNull('parent_id')
            ->values();
    }

    public function allActiveCategories(): Collection
    {
        if ($this->shouldBypassCache()) {
            return $this->fetchAllActiveCategories();
        }

        $cached = $this->safeCacheGet('catalog:categories:flat');
        if ($cached instanceof Collection) {
            return $cached;
        }

        $fresh = $this->fetchAllActiveCategories();
        $this->safeCachePut('catalog:categories:flat', $fresh);

        return $fresh;
    }

    private function fetchAllActiveCategories(): Collection
    {
        return CatalogCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function activeBrands(): Collection
    {
        if ($this->shouldBypassCache()) {
            return $this->fetchActiveBrands();
        }

        $cached = $this->safeCacheGet(self::KEY_BRANDS);
        if ($cached instanceof Collection) {
            return $cached;
        }

        $fresh = $this->fetchActiveBrands();
        $this->safeCachePut(self::KEY_BRANDS, $fresh);

        return $fresh;
    }

    private function fetchActiveBrands(): Collection
    {
        return Brand::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function settings(): CatalogSetting
    {
        if ($this->shouldBypassCache()) {
            return CatalogSetting::current();
        }

        $cached = $this->safeCacheGet(self::KEY_SETTINGS);
        if ($cached instanceof CatalogSetting) {
            return $cached;
        }

        $settings = CatalogSetting::current();
        $this->safeCachePut(self::KEY_SETTINGS, $settings);

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

    /**
     * @template T
     *
     * @param  class-string<T>|null  $expected
     * @return T|null
     */
    private function safeCacheGet(string $key, ?string $expected = null)
    {
        try {
            $value = cache()->get($key);
        } catch (\Throwable) {
            return null;
        }

        if ($value === null) {
            return null;
        }

        if ($expected !== null && ! ($value instanceof $expected)) {
            return null;
        }

        return $value;
    }

    private function safeCachePut(string $key, mixed $value): void
    {
        try {
            cache()->put($key, $value, self::TTL);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
