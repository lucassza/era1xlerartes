<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostRatingAggregator
{
    public function average(Post $post): ?float
    {
        return Cache::remember(
            app(EngagementService::class)->ratingCacheKey($post),
            now()->addMinutes(10),
            fn (): ?float => $post->averageRating(),
        );
    }

    public function count(Post $post): int
    {
        return (int) Cache::remember(
            sprintf('post:%s:rating:count', $post->getKey()),
            now()->addMinutes(10),
            fn (): int => $post->ratingsCount(),
        );
    }

    /**
     * @return array<int, int>
     */
    public function distribution(Post $post): array
    {
        return Cache::remember(
            sprintf('post:%s:rating:distribution', $post->getKey()),
            now()->addMinutes(10),
            fn (): array => $post->ratingDistribution(),
        );
    }
}
