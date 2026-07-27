<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_catalog_with_published_products(): void
    {
        $published = Product::factory()->count(3)->published()->create();
        Product::factory()->create(['status' => ProductStatus::Draft]);
        CatalogSetting::current()->fill(['title' => 'Catálogo teste'])->save();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('catalog.home');
        $response->assertViewHas('settings');
        $response->assertViewHas('latest');
        $response->assertSee('Catálogo teste', false);
        foreach ($published as $product) {
            $response->assertSee((string) $product->name);
        }
    }

    public function test_home_page_provides_landmarks_skip_link_and_primary_navigation(): void
    {
        CatalogSetting::current();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('Pular para o conteúdo principal', false);
        $response->assertSee('<header', false);
        $response->assertSee('<main id="main-content" tabindex="-1">', false);
        $response->assertSee('<footer', false);
        $response->assertSee('aria-label="Navegação principal"', false);
        $response->assertSee('aria-label="Navegação móvel"', false);
        $response->assertSee('aria-label="Links do rodapé"', false);
        $response->assertSee('href="'.route('home').'"', false);
        $response->assertSee('href="'.route('search.index').'"', false);
        self::assertSame(1, substr_count($response->getContent(), 'id="main-content"'));
    }

    public function test_home_page_does_not_show_unpublished_products(): void
    {
        CatalogSetting::current();
        $published = Product::factory()->published()->create(['name' => 'Produto público']);
        Product::factory()->create(['name' => 'Produto Rascunho Oculto', 'status' => ProductStatus::Draft]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee($published->name);
        $response->assertDontSee('Produto Rascunho Oculto');
    }

    public function test_home_lists_active_catalog_categories(): void
    {
        CatalogSetting::current();
        CatalogCategory::factory()->create(['name' => 'Móveis Boutique', 'is_active' => true]);
        CatalogCategory::factory()->create(['name' => 'Escondida', 'is_active' => false]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Móveis Boutique');
        $response->assertDontSee('Escondida');
    }
}
