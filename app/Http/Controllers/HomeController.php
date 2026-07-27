<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Models\BlogSetting;
use App\Models\Post;
use App\Models\Testimonial;
use App\Services\PostRecommendationService;
use App\Services\PublicNavigationService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(
        PostRecommendationService $recommendationService,
        PublicNavigationService $navigationService,
        SeoService $seoService,
    ): View {
        $settings = BlogSetting::query()->firstOrCreate([], [
            'site_name' => config('app.name', 'TDB Mix Blog'),
            'site_description' => 'Blog moderno em Laravel.',
            'default_meta_title' => config('app.name', 'TDB Mix Blog'),
            'default_meta_description' => 'Blog moderno em Laravel.',
            'newsletter_enabled' => true,
        ]);

        $recentPosts = Post::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->orderByRaw('CASE WHEN source_platform = ? THEN 0 ELSE 1 END', ['tdbmix-blogger'])
            ->latest('published_at')
            ->limit(12)
            ->get();

        $heroPost = $recentPosts->first();
        $featuredPosts = $recentPosts->slice(1, 2)->values();
        $listingPosts = $recentPosts->slice(3)->values();
        $popularPosts = $recommendationService->popularPosts();

        $categoryTopics = $navigationService->categories()->map(function ($category) {
            $posts = $category->posts()
                ->where('status', PostStatus::Published)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereNull('archived_at')
                ->with(['author', 'category', 'tags'])
                ->latest('published_at')
                ->limit(6)
                ->get();

            return ['category' => $category, 'posts' => $posts];
        })->filter(fn ($item) => $item['posts']->isNotEmpty())->values();

        $testimonials = Testimonial::query()
            ->approved()
            ->latest('approved_at')
            ->limit(6)
            ->get();

        $seo = $seoService->forHome($settings);

        return view('welcome', [
            'heroPost' => $heroPost,
            'featuredPosts' => $featuredPosts,
            'listingPosts' => $listingPosts,
            'recentPosts' => $recentPosts,
            'popularPosts' => $popularPosts,
            'categoryTopics' => $categoryTopics,
            'testimonials' => $testimonials,
            'testimonialsEnabled' => $settings->testimonials_enabled ?? true,
            'newsletterEnabled' => $settings->newsletter_enabled,
            'meta' => $seo['meta'],
            'jsonLd' => $seo['jsonLd'],
        ]);
    }
}
