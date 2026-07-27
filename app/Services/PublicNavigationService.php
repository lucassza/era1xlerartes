<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class PublicNavigationService
{
    /**
     * @return EloquentCollection<int, Category>
     */
    public function categories(): EloquentCollection
    {
        return Category::query()
            ->where('is_active', true)
            ->whereHas('posts', static fn ($query) => $query->published())
            ->withCount([
                'posts as published_posts_count' => static fn ($query) => $query->published(),
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
            ->whereHas('posts', static fn ($query) => $query->published())
            ->withCount([
                'posts as published_posts_count' => static fn ($query) => $query->published(),
            ])
            ->orderByDesc('published_posts_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
