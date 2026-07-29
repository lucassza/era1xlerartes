<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_published_products_and_active_categories(): void
    {
        $product = Product::factory()->published()->create(['updated_at' => now()]);
        Product::factory()->create(['name' => 'Produto Oculto', 'status' => ProductStatus::Draft]);
        $activeCategory = CatalogCategory::factory()->create(['name' => 'Categoria Ativa', 'is_active' => true]);
        CatalogCategory::factory()->create(['name' => 'Categoria Oculta', 'is_active' => false]);

        $response = $this->get(route('seo.sitemap'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/xml; charset=UTF-8');
        $response->assertSee('<loc>'.route('products.index').'</loc>', false);
        $response->assertSee('<loc>'.route('products.show', $product->slug).'</loc>', false);
        $response->assertSee('<loc>'.route('catalog-categories.show', $activeCategory->slug).'</loc>', false);
        $response->assertDontSee('categoria-oculta', false);
        $response->assertDontSee('produto-oculto', false);
    }

    public function test_robots_txt_contains_sitemap_and_blocks_admin(): void
    {
        $response = $this->get(route('seo.robots'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *', false);
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Sitemap: '.route('seo.sitemap'), false);
    }

    public function test_product_page_emits_breadcrumb_jsonld(): void
    {
        $category = CatalogCategory::factory()->create(['name' => 'Móveis Boutique']);
        $product = Product::factory()->published()->create([
            'name' => 'Sofá Oslo Premium',
            'catalog_category_id' => $category->id,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        $response->assertSee('"name":"Início"', false);
        $response->assertSee('"name":"Produtos"', false);
        $response->assertSee('"name":"Móveis Boutique"', false);
        $response->assertSee('"name":"Sofá Oslo Premium"', false);
        $response->assertSee('"position":4', false);
    }

    public function test_category_page_emits_breadcrumb_jsonld(): void
    {
        $category = CatalogCategory::factory()->create(['name' => 'Iluminação Premium']);

        $response = $this->get(route('catalog-categories.show', $category->slug));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        $response->assertSee('"name":"Iluminação Premium"', false);
        $response->assertSee('"position":3', false);
    }

    public function test_product_og_and_twitter_meta_tags_include_image_and_canonical(): void
    {
        $product = Product::factory()->published()->create();
        $brand = Brand::factory()->create(['name' => 'Casa Verde Premium']);
        $product->update(['brand_id' => $brand->id]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('<meta property="og:type" content="product">', false);
        $response->assertSee('<meta property="og:url" content="'.route('products.show', $product->slug).'">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<link rel="canonical" href="'.route('products.show', $product->slug).'">', false);
        $response->assertSee('"@type":"Brand"', false);
        $response->assertSee('"name":"Casa Verde Premium"', false);
    }
}
