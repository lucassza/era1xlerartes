<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Author;
use App\Models\BlogSetting;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Collection;

final class SeoService
{
    /**
     * @return array{meta: array<string, mixed>, jsonLd: array<string, mixed>}
     */
    public function forHome(BlogSetting $settings): array
    {
        $siteName = $settings->site_name ?: config('app.name', 'TDB Mix Blog');
        $description = $settings->default_meta_description ?: $settings->site_description ?: 'Blog moderno em Laravel.';
        $siteUrl = url('/');
        $meta = [
            'title' => $settings->default_meta_title ?: $siteName,
            'description' => $description,
            'canonical' => $siteUrl,
            'robots' => 'index,follow',
            'og' => [
                'title' => $settings->default_meta_title ?: $siteName,
                'description' => $description,
                'type' => 'website',
                'url' => $siteUrl,
            ],
            'twitter' => [
                'card' => 'summary',
                'title' => $settings->default_meta_title ?: $siteName,
                'description' => $description,
            ],
        ];

        $graph = [];

        if (filled($siteName) && filter_var($siteUrl, FILTER_VALIDATE_URL)) {
            $graph[] = [
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $siteUrl,
            ];
            $graph[] = [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => $siteUrl,
            ];
        }

        return [
            'meta' => $meta,
            'jsonLd' => $graph === [] ? [] : [
                '@context' => 'https://schema.org',
                '@graph' => $graph,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forPost(Post $post): array
    {
        $title = sprintf('%s | %s', $post->title, config('app.name'));
        $description = $post->excerpt ?: str($post->content)->stripTags()->squish()->limit(160)->toString();

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => route('posts.show', $post->slug),
            'robots' => 'index,follow',
            'og' => [
                'title' => $title,
                'description' => $description,
                'type' => 'article',
                'url' => route('posts.show', $post->slug),
                'image' => $this->coverUrl($post),
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $this->coverUrl($post),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function blogPostingJsonLd(Post $post): array
    {
        $author = $post->author;
        $publishedAt = $post->published_at;
        $canonical = route('posts.show', $post->slug);

        if (! $author instanceof Author || blank($post->title) || ! $publishedAt || ! filter_var($canonical, FILTER_VALIDATE_URL)) {
            return [];
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->excerpt ?: str($post->content)->stripTags()->squish()->limit(160)->toString(),
            'datePublished' => $publishedAt->toIso8601String(),
            'mainEntityOfPage' => $canonical,
            'author' => [
                '@type' => 'Person',
                'name' => $author->name,
                'url' => route('authors.show', $author->slug),
            ],
        ];

        if ($publisherName = config('app.name')) {
            $jsonLd['publisher'] = [
                '@type' => 'Organization',
                'name' => $publisherName,
                'url' => url('/'),
            ];
        }

        if ($coverUrl = $this->coverUrl($post)) {
            $jsonLd['image'] = [$coverUrl];
        }

        if ($keywords = $post->tags->pluck('name')->filter()->implode(', ')) {
            $jsonLd['keywords'] = $keywords;
        }

        $category = $post->category;

        if ($category instanceof Category && filled($category->name) && filled($category->slug)) {
            $jsonLd['breadcrumb'] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Início',
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => $category->name,
                        'item' => route('categories.show', $category->slug),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $post->title,
                        'item' => $canonical,
                    ],
                ],
            ];
        }

        return $jsonLd;
    }

    private function coverUrl(Post $post): ?string
    {
        $cover = $post->getFirstMedia('cover');

        if (! $cover) {
            return null;
        }

        $url = $cover->hasGeneratedConversion('thumb-1200')
            ? $cover->getFullUrl('thumb-1200')
            : $cover->getFullUrl();

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * @return array<int, array{loc:string,lastmod:?string}>
     */
    public function sitemapUrls(): array
    {
        $urls = [
            [
                'loc' => url('/'),
                'lastmod' => now()->toDateString(),
            ],
        ];

        Post::query()->published()->latest('updated_at')->get()->each(function (Post $post) use (&$urls): void {
            $urls[] = [
                'loc' => route('posts.show', $post->slug),
                'lastmod' => optional($post->updated_at)?->toDateString(),
            ];
        });

        Category::query()->where('is_active', true)->get()->each(function (Category $category) use (&$urls): void {
            $urls[] = [
                'loc' => route('categories.show', $category->slug),
                'lastmod' => optional($category->updated_at)?->toDateString(),
            ];
        });

        Tag::query()->where('is_active', true)->get()->each(function (Tag $tag) use (&$urls): void {
            $urls[] = [
                'loc' => route('tags.show', $tag->slug),
                'lastmod' => optional($tag->updated_at)?->toDateString(),
            ];
        });

        Author::query()->where('is_active', true)->get()->each(function (Author $author) use (&$urls): void {
            $urls[] = [
                'loc' => route('authors.show', $author->slug),
                'lastmod' => optional($author->updated_at)?->toDateString(),
            ];
        });

        return $urls;
    }

    /**
     * @return array<string, string>
     */
    public function rssChannel(): array
    {
        return [
            'title' => config('app.name', 'TDB Mix Blog'),
            'link' => url('/'),
            'description' => 'Feed oficial do blog.',
            'language' => 'pt-BR',
        ];
    }

    /**
     * @return Collection<int, Post>
     */
    public function rssItems(): Collection
    {
        return Post::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->latest('published_at')
            ->limit(20)
            ->get();
    }
}
