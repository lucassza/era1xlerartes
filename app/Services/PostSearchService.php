<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Expression;

class PostSearchService
{
    public function searchPublished(string $term, int $limit = 10): Collection
    {
        $term = trim($term);
        $similarityThreshold = 0.12;

        if ($term === '') {
            return new Collection;
        }

        return Post::query()
            ->published()
            ->select('*')
            ->selectRaw(
                "ts_rank_cd(search_tsv, websearch_to_tsquery('public.pt_unaccent', ?), 32) as search_rank",
                [$term],
            )
            ->selectRaw(
                'GREATEST(similarity(immutable_unaccent(title), immutable_unaccent(?)), similarity(immutable_unaccent(slug), immutable_unaccent(?))) as trigram_rank',
                [$term, $term],
            )
            ->where(static function ($query) use ($term, $similarityThreshold): void {
                $query
                    ->whereRaw("search_tsv @@ websearch_to_tsquery('public.pt_unaccent', ?)", [$term])
                    ->orWhereRaw('similarity(immutable_unaccent(title), immutable_unaccent(?)) >= ?', [$term, $similarityThreshold])
                    ->orWhereRaw('similarity(immutable_unaccent(slug), immutable_unaccent(?)) >= ?', [$term, $similarityThreshold]);
            })
            ->orderByRaw(
                "CASE WHEN search_tsv @@ websearch_to_tsquery('public.pt_unaccent', ?) THEN 0 ELSE 1 END",
                [$term],
            )
            ->orderByDesc(new Expression('search_rank'))
            ->orderByDesc(new Expression('trigram_rank'))
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
}
