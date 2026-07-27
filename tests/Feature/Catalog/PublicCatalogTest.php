<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_catalog_home_with_published_products(): void
    {
        $published = Product::factory()->count(3)->published()->create();
        Product::factory()->create(['status' => ProductStatus::Draft]);

        $response = $this->get(route('home'));

        $response->assertOk();
        foreach ($published as $product) {
            $response->assertSee((string) $product->name);
        }
    }

    public function test_products_index_lists_only_publicly_visible_products(): void
    {
        $visible = Product::factory()->published()->create(['name' => 'Visível']);
        Product::factory()->create(['name' => 'Rascunho Oculto', 'status' => ProductStatus::Draft]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Visível');
        $response->assertDontSee('Rascunho Oculto');
    }

    public function test_products_index_filters_by_search_term(): void
    {
        Product::factory()->published()->create(['name' => 'Sofá Oslo Premium']);
        Product::factory()->published()->create(['name' => 'Mesa de Jantar']);

        $response = $this->get(route('products.index', ['buscar' => 'Sofá']));

        $response->assertOk();
        $response->assertSee('Sofá Oslo Premium');
        $response->assertDontSee('Mesa de Jantar');
    }

    public function test_products_index_filters_by_brand(): void
    {
        $brand = Brand::factory()->create(['slug' => 'lumera']);
        Product::factory()->published()->create(['name' => 'Luminária A', 'brand_id' => $brand->id]);
        Product::factory()->published()->create(['name' => 'Outro Item']);

        $response = $this->get(route('products.index', ['marca' => 'lumera']));

        $response->assertOk();
        $response->assertSee('Luminária A');
        $response->assertDontSee('Outro Item');
    }

    public function test_product_show_returns_404_for_draft(): void
    {
        $draft = Product::factory()->create(['status' => ProductStatus::Draft]);

        $response = $this->get(route('products.show', $draft->slug));

        $response->assertNotFound();
    }

    public function test_product_show_renders_metadata_and_jsonld(): void
    {
        $product = Product::factory()->published()->create();

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.route('products.show', $product->slug).'">', false);
        $response->assertSee('<meta property="og:type" content="product">', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Product"', false);
    }

    public function test_product_show_with_brand_includes_brand_in_jsonld(): void
    {
        $brand = Brand::factory()->create(['name' => 'Oficce+']);
        $product = Product::factory()->published()->create(['brand_id' => $brand->id]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('"@type":"Brand"', false);
        $response->assertSee('"name":"Oficce+"', false);
    }

    public function test_category_show_lists_products_and_subcategories(): void
    {
        $root = CatalogCategory::factory()->create(['name' => 'Móveis', 'slug' => 'moveis']);
        $child = CatalogCategory::factory()->create(['name' => 'Sofás', 'parent_id' => $root->id, 'slug' => 'sofas']);
        Product::factory()->published()->create(['name' => 'Sofá Oslo', 'catalog_category_id' => $child->id]);
        Product::factory()->published()->create(['name' => 'Mesa Bistro', 'catalog_category_id' => $root->id]);

        $response = $this->get(route('catalog-categories.show', 'moveis'));

        $response->assertOk();
        $response->assertSee('Móveis');
        $response->assertSee('Sofás');
        $response->assertSee('Sofá Oslo');
        $response->assertSee('Mesa Bistro');
    }

    public function test_inactive_category_returns_404(): void
    {
        CatalogCategory::factory()->create(['slug' => 'escondida', 'is_active' => false]);

        $response = $this->get(route('catalog-categories.show', 'escondida'));

        $response->assertNotFound();
    }

    public function test_blog_route_preserves_existing_articles(): void
    {
        Post::factory()->published()->create(['title' => 'Artigo preservado']);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('Artigo preservado');
    }

    public function test_blog_route_returns_post_detail_with_unchanged_url(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee($post->title);
    }
}
