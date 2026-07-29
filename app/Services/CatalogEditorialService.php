<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Collection;

class CatalogEditorialService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeData(array $data): array
    {
        $status = $data['status'] ?? ProductStatus::Draft->value;
        if (! $status) {
            $status = ProductStatus::Draft->value;
        }

        $data['status'] = $status;

        if ($status === ProductStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($status !== ProductStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function syncMedia(Product $product, array $data): void
    {
        if (! empty($data['cover'])) {
            $product->clearMediaCollection('cover');
            $product->addMediaFromDisk($data['cover'], config('media-library.disk_name'))
                ->toMediaCollection('cover');
        }

        if (! empty($data['gallery'])) {
            $product->clearMediaCollection('gallery');
            foreach ((array) $data['gallery'] as $path) {
                $product->addMediaFromDisk($path, config('media-library.disk_name'))
                    ->toMediaCollection('gallery');
            }
        }

        if (! empty($data['attachments'])) {
            $product->clearMediaCollection('attachments');
            foreach ((array) $data['attachments'] as $path) {
                $product->addMediaFromDisk($path, config('media-library.disk_name'))
                    ->toMediaCollection('attachments');
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function syncAttributes(Product $product, array $data): void
    {
        $rows = $data['attribute_values'] ?? [];
        if (! is_array($rows)) {
            return;
        }

        $keepIds = collect($rows)
            ->filter(fn ($r) => is_array($r) && ! empty($r['attribute_id']))
            ->map(fn (array $r) => ProductAttributeValue::query()->updateOrCreate(
                ['product_id' => $product->id, 'attribute_id' => $r['attribute_id']],
                ['value' => $r['value'] ?? null],
            )->id)
            ->all();

        $product->attributeValues()->whereNotIn('id', $keepIds)->delete();
    }

    /**
     * @return Collection<int, CatalogCategory>
     */
    public function categoriesForSelect(): Collection
    {
        return CatalogCategory::query()
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (CatalogCategory $category): CatalogCategory {
                $parentName = $category->getAttribute('parent')?->getAttribute('name');
                $name = (string) $category->getAttribute('name');
                $category->setAttribute(
                    'name',
                    $parentName ? "{$parentName} > {$name}" : $name,
                );

                return $category;
            });
    }
}
