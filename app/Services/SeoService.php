<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Author;
use App\Models\BlogSetting;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class SeoService
{
    /**
     * @return array{meta: array<string, mixed>, jsonLd: array<string, mixed>}
     */
    public function forHome(BlogSetting $settings): array
    {
        $siteName = $settings->site_name ?: config('app.name', 'Era1x Ler Artes');
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
            [
                'loc' => route('products.index'),
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

        Product::query()->publiclyVisible()->get()->each(function (Product $product) use (&$urls): void {
            $urls[] = [
                'loc' => route('products.show', $product->slug),
                'lastmod' => optional($product->updated_at)?->toDateString(),
            ];
        });

        CatalogCategory::query()->where('is_active', true)->get()->each(function (CatalogCategory $category) use (&$urls): void {
            $urls[] = [
                'loc' => route('catalog-categories.show', $category->slug),
                'lastmod' => optional($category->updated_at)?->toDateString(),
            ];
        });

        return $urls;
    }

    /**
     * @return array{meta: array<string, mixed>, jsonLd: array<int|string, mixed>}
     */
    public function forCatalogHome(CatalogSetting $settings): array
    {
        $title = (string) $settings->getAttribute('seo_title')
            ?: (string) $settings->getAttribute('hero_title')
            ?: (string) $settings->getAttribute('title');
        $description = (string) $settings->getAttribute('seo_description') ?: (string) $settings->getAttribute('intro_text') ?: '';
        $siteName = config('app.name', 'Era1x Ler Artes');

        $meta = [
            'title' => $title ? "{$title} | {$siteName}" : $siteName,
            'description' => $description,
            'canonical' => url('/'),
            'robots' => 'index,follow',
            'og' => [
                'title' => $title,
                'description' => $description,
                'type' => 'website',
                'url' => url('/'),
            ],
            'twitter' => [
                'card' => 'summary',
                'title' => $title,
                'description' => $description,
            ],
        ];

        return [
            'meta' => $meta,
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => url('/'),
            ],
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, jsonLd: array<int|string, mixed>}
     */
    public function forCatalogIndex(Request $request, CatalogSetting $settings): array
    {
        $title = (string) $settings->getAttribute('seo_title') ?: (string) $settings->getAttribute('title');
        $siteName = config('app.name', 'Era1x Ler Artes');
        $term = trim((string) $request->query('buscar', ''));

        $heading = $term !== '' ? "Resultados para \"{$term}\"" : 'Produtos';

        return [
            'meta' => [
                'title' => "{$heading} | {$title} | {$siteName}",
                'description' => 'Catálogo de produtos de '.$siteName,
                'canonical' => url('/produtos'),
                'robots' => $term !== '' ? 'noindex,follow' : 'index,follow',
                'og' => [
                    'title' => "{$heading} | {$title}",
                    'description' => 'Catálogo de produtos',
                    'type' => 'website',
                    'url' => url('/produtos'),
                ],
                'twitter' => [
                    'card' => 'summary',
                    'title' => "{$heading} | {$title}",
                    'description' => 'Catálogo de produtos',
                ],
            ],
            'jsonLd' => [],
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, jsonLd: array<int|string, mixed>}
     */
    public function forCatalogCategory(CatalogCategory $category, CatalogSetting $settings): array
    {
        $title = (string) $category->getAttribute('seo_title') ?: (string) $category->getAttribute('name');
        $description = (string) $category->getAttribute('seo_description') ?: (string) $category->getAttribute('description');
        $siteName = config('app.name', 'Era1x Ler Artes');

        $breadcrumbItems = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Produtos', 'url' => route('products.index')],
            ['name' => $title, 'url' => route('catalog-categories.show', $category->slug)],
        ];

        return [
            'meta' => [
                'title' => "{$title} | {$siteName}",
                'description' => $description ?: "Produtos da categoria {$title}",
                'canonical' => route('catalog-categories.show', $category->slug),
                'robots' => 'index,follow',
                'og' => [
                    'title' => $title,
                    'description' => $description,
                    'type' => 'website',
                    'url' => route('catalog-categories.show', $category->slug),
                ],
                'twitter' => [
                    'card' => 'summary',
                    'title' => $title,
                    'description' => $description,
                ],
            ],
            'jsonLd' => $this->breadcrumbList($breadcrumbItems),
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, jsonLd: array<int|string, mixed>}
     */
    public function forProduct(Product $product, CatalogSetting $settings): array
    {
        $name = (string) $product->getAttribute('name');
        $siteName = config('app.name', 'Era1x Ler Artes');
        $title = (string) $product->getAttribute('seo_title') ?: $name;
        $description = (string) $product->getAttribute('seo_description') ?: (string) $product->getAttribute('short_description') ?: '';
        $image = $product->getFirstMediaUrl('cover');

        $meta = [
            'title' => "{$title} | {$siteName}",
            'description' => $description,
            'canonical' => route('products.show', $product->slug),
            'robots' => 'index,follow',
            'og' => [
                'title' => $title,
                'description' => $description,
                'type' => 'product',
                'url' => route('products.show', $product->slug),
                'image' => $image ?: null,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $image ?: null,
            ],
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'sku' => $product->getAttribute('sku'),
            'description' => $description,
            'image' => $image ?: null,
            'url' => route('products.show', $product->slug),
        ];

        $brand = $product->getRelation('brand');
        if ($brand instanceof Brand) {
            $jsonLd['brand'] = [
                '@type' => 'Brand',
                'name' => (string) $brand->getAttribute('name'),
            ];
        }

        $category = $product->getRelation('category');
        $breadcrumbItems = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Produtos', 'url' => route('products.index')],
        ];
        if ($category instanceof CatalogCategory) {
            $categoryTitle = (string) $category->getAttribute('name');
            $breadcrumbItems[] = [
                'name' => $categoryTitle,
                'url' => route('catalog-categories.show', $category->slug),
            ];
        }
        $breadcrumbItems[] = ['name' => $name, 'url' => route('products.show', $product->slug)];

        $jsonLd['breadcrumb'] = $this->breadcrumbList($breadcrumbItems);

        return [
            'meta' => $meta,
            'jsonLd' => $jsonLd,
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        $list = [];
        foreach (array_values($items) as $position => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => (string) $item['name'],
                'item' => (string) $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rssChannel(): array
    {
        return [
            'title' => config('app.name', 'Era1x Ler Artes'),
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
