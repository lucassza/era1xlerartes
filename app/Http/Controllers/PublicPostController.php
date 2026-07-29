<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostRecommendationService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PublicPostController extends Controller
{
    public function show(string $slug, SeoService $seoService, PostRecommendationService $recommendationService): View|Response
    {
        $post = Post::query()
            ->published()
            ->with(['author.user', 'category', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $post->increment('views_count');

        $relatedPosts = $recommendationService->relatedPosts($post);
        $popularPosts = $recommendationService->popularPosts();

        return response()->view('posts.show', [
            'post' => $post,
            'meta' => $seoService->forPost($post),
            'jsonLd' => $seoService->blogPostingJsonLd($post),
            'relatedPosts' => $relatedPosts,
            'popularPosts' => $popularPosts,
        ]);
    }
}
