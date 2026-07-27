<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_page_renders_meta_tags_and_json_ld(): void
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);

        $post = Post::factory()->published()->create([
            'title' => 'SEO com Laravel',
            'excerpt' => 'Resumo otimizado para SEO.',
        ]);

        $post->tags()->attach($tag);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee('<title>SEO com Laravel | '.config('app.name').'</title>', false);
        $response->assertSee('<meta name="description" content="Resumo otimizado para SEO.">', false);
        $response->assertSee('<link rel="canonical" href="'.route('posts.show', $post->slug).'">', false);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<link rel="alternate" type="application/rss+xml"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('BlogPosting', false);
        $response->assertSee('BreadcrumbList', false);
        $response->assertSee('SEO com Laravel', false);
        $response->assertSee('"datePublished":"'.$post->published_at?->toIso8601String().'"', false);
        $response->assertDontSee('"dateModified"', false);

        $this->assertStructuredDataIsValidJson($response->getContent());

        $jsonLd = $this->structuredData($response->getContent());
        self::assertSame('BlogPosting', $jsonLd['@type']);
        self::assertSame($post->title, $jsonLd['headline']);
        self::assertSame(route('posts.show', $post->slug), $jsonLd['mainEntityOfPage']);
        self::assertSame('BreadcrumbList', $jsonLd['breadcrumb']['@type']);
    }

    public function test_home_page_renders_consistent_metadata_and_valid_structured_data(): void
    {
        $canonical = route('home');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.$canonical.'">', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:url" content="'.$canonical.'">', false);
        $response->assertSee('<meta name="twitter:card" content="summary">', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"@type":"Organization"', false);

        $this->assertStructuredDataIsValidJson($response->getContent());

        $jsonLd = $this->structuredData($response->getContent());
        $graph = $jsonLd['@graph'] ?? null;
        self::assertIsArray($graph);
        self::assertArrayHasKey(0, $graph);
        self::assertIsArray($graph[0]);
        self::assertSame('https://schema.org', $jsonLd['@context']);
        self::assertSame(['WebSite', 'Organization'], array_column($graph, '@type'));
        self::assertSame($canonical, $graph[0]['url']);
        $response->assertDontSee('"@type":"BlogPosting"', false);
    }

    public function test_unpublished_posts_do_not_expose_public_metadata_or_structured_data(): void
    {
        $post = Post::factory()->create(['title' => 'Rascunho privado']);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertNotFound();
        $response->assertDontSee('Rascunho privado');
        $response->assertDontSee('application/ld+json', false);
    }

    private function assertStructuredDataIsValidJson(string $content): void
    {
        $jsonLd = $this->structuredData($content);

        self::assertIsArray($jsonLd);
        self::assertSame(JSON_ERROR_NONE, json_last_error());
    }

    /**
     * @return array<string, mixed>
     */
    private function structuredData(string $content): array
    {
        self::assertSame(1, preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $content, $matches));

        $jsonLd = json_decode($matches[1] ?? '', true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($jsonLd);

        return $jsonLd;
    }

    public function test_sitemap_lists_public_urls(): void
    {
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $tag = Tag::factory()->create();
        $post = Post::factory()->published()->create([
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);
        $post->tags()->attach($tag);

        $response = $this->get(route('seo.sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(route('posts.show', $post->slug), false);
        $response->assertSee(route('categories.show', $category->slug), false);
        $response->assertSee(route('tags.show', $tag->slug), false);
        $response->assertSee(route('authors.show', $author->slug), false);
    }

    public function test_rss_exposes_published_posts_only(): void
    {
        $published = Post::factory()->published()->create(['title' => 'Publicado']);
        Post::factory()->create(['title' => 'Rascunho']);

        $response = $this->get(route('seo.rss'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->assertSee('Publicado');
        $response->assertDontSee('Rascunho');
        $response->assertSee(route('posts.show', $published->slug), false);
    }
}
