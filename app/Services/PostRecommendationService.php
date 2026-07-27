<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Collection;

class PostRecommendationService
{
    /**
     * @return Collection<int, Post>
     */
    public function relatedPosts(Post $post, int $limit = 4): Collection
    {
        $tagIds = $post->tags->pluck('id')->all();

        $related = Post::query()
            ->published()
            ->with(['author.user', 'category', 'tags'])
            ->whereKeyNot($post->getKey())
            ->when($tagIds !== [], function ($query) use ($tagIds): void {
                $query->whereHas('tags', static fn ($tagQuery) => $tagQuery->whereIn('tags.id', $tagIds));
            }, function ($query) use ($post): void {
                $query->where('category_id', $post->category_id);
            })
            ->latest('published_at')
            ->limit($limit)
            ->get();

        if ($related->count() >= $limit) {
            return $related;
        }

        $fallback = Post::query()
            ->published()
            ->with(['author.user', 'category', 'tags'])
            ->whereKeyNot($post->getKey())
            ->whereNotIn('id', $related->pluck('id'))
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->limit($limit - $related->count())
            ->get();

        return $related->concat($fallback)->values();
    }

    /**
     * @return Collection<int, Post>
     */
    public function popularPosts(int $limit = 5): Collection
    {
        return Post::query()
            ->published()
            ->with(['author.user', 'category', 'tags'])
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
}
