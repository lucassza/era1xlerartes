<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PublicNavigationService
{
    /**
     * @return EloquentCollection<int, Category>
     */
    public function categories(): EloquentCollection
    {
        return Category::query()
            ->where('is_active', true)
            ->whereHas('posts', self::publishedPosts(...))
            ->withCount([
                'posts as published_posts_count' => self::publishedPosts(...),
            ])
            ->orderByDesc('published_posts_count')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return EloquentCollection<int, Tag>
     */
    public function tags(int $limit = 8): EloquentCollection
    {
        return Tag::query()
            ->where('is_active', true)
            ->whereHas('posts', self::publishedPosts(...))
            ->withCount([
                'posts as published_posts_count' => self::publishedPosts(...),
            ])
            ->orderByDesc('published_posts_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    private static function publishedPosts(Builder $query): Builder
    {
        return $query->published();
    }

    /**
     * @return Collection<int, CatalogCategory>
     */
    public function catalogTopCategories(int $limit = 8): Collection
    {
        return app(CatalogCacheService::class)->activeCategories()
            ->filter(fn ($c) => $c->getAttribute('parent_id') === null)
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, Brand>
     */
    public function activeBrands(): Collection
    {
        return app(CatalogCacheService::class)->activeBrands();
    }
}
