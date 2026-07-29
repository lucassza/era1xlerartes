<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Models\Product;
use App\Models\ProductInquiry;
use Database\Seeders\CatalogDemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_categories_products_and_inquiries_without_external_services(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CatalogDemoSeeder::class);

        $this->assertGreaterThanOrEqual(4, CatalogCategory::query()->count());
        $this->assertGreaterThanOrEqual(8, CatalogCategory::query()->whereNotNull('parent_id')->count());
        $this->assertGreaterThanOrEqual(4, Brand::query()->count());
        $this->assertGreaterThanOrEqual(10, Product::query()->count());

        $this->assertNotNull(CatalogSetting::current());

        $this->assertGreaterThan(0, ProductInquiry::query()->count());
        $this->assertGreaterThan(0, ProductInquiry::query()->where('status', 'new')->count());
        $this->assertGreaterThan(0, ProductInquiry::query()->where('status', 'in_service')->count());
        $this->assertGreaterThan(0, ProductInquiry::query()->where('status', 'completed')->count());
        $this->assertGreaterThan(0, ProductInquiry::query()->where('status', 'spam')->count());
    }
}
