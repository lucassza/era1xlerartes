<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_includes_primary_navigation_entries(): void
    {
        CatalogSetting::current();
        CatalogCategory::factory()->create(['name' => 'Móveis Boutique', 'is_active' => true]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('href="'.route('home').'"', false);
        $response->assertSee('href="'.route('products.index').'"', false);
        $response->assertSee('href="'.route('blog.index').'"', false);
        $response->assertSee('href="'.route('site.contact').'"', false);
        $response->assertSee('Móveis Boutique');
    }

    public function test_header_highlights_active_route_with_aria_current(): void
    {
        CatalogSetting::current();

        $response = $this->get(route('home'));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('aria-current="page"', $content);
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*Início/u', $content);
    }

    public function test_contact_route_renders_contact_page(): void
    {
        CatalogSetting::current()->fill([
            'commercial_phone' => '+55 11 4002-8922',
            'commercial_whatsapp' => '+55 11 99999 1111',
            'inquiry_recipients' => 'comercial@example.com, vendas@example.com',
        ])->save();

        $response = $this->get(route('site.contact'));

        $response->assertOk();
        $response->assertSee('+55 11 4002-8922');
        $response->assertSee('+55 11 99999 1111');
        $response->assertSee('comercial@example.com');
        $response->assertSee('wa.me/5511999991111', false);
    }

    public function test_blog_index_route_lists_published_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Artigo Público 1']);
        Post::factory()->published()->create(['title' => 'Artigo Público 2']);
        Post::factory()->create(['title' => 'Rascunho Oculto']);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('Artigo Público 1');
        $response->assertSee('Artigo Público 2');
        $response->assertDontSee('Rascunho Oculto');
    }

    public function test_blog_post_route_remains_intact(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee($post->title);
    }

    public function test_rss_route_still_responds(): void
    {
        Post::factory()->published()->create();

        $response = $this->get(route('seo.rss'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/rss+xml; charset=UTF-8');
    }

    public function test_categories_route_remains_intact(): void
    {
        $cat = Category::factory()->create(['slug' => 'design', 'is_active' => true]);
        Post::factory()->published()->create(['title' => 'Post Design', 'category_id' => $cat->id]);

        $response = $this->get(route('categories.show', 'design'));

        $response->assertOk();
        $response->assertSee('Post Design');
    }
}
