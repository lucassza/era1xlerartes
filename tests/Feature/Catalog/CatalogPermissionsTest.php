<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_catalog_permissions_are_seeded_idempotently(): void
    {
        $expected = [
            'catalog.products.view',
            'catalog.products.create',
            'catalog.products.update',
            'catalog.products.delete',
            'catalog.products.publish',
            'catalog.categories.manage',
            'catalog.brands.manage',
            'catalog.attributes.manage',
            'catalog.inquiries.view',
            'catalog.inquiries.update',
            'catalog.inquiries.assign',
            'catalog.inquiries.export',
            'catalog.settings.manage',
        ];

        foreach ($expected as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission, 'guard_name' => 'web']);
        }

        $this->seed(RolesAndPermissionsSeeder::class);

        $count = \DB::table('permissions')->whereIn('name', $expected)->count();
        $this->assertSame(count($expected), $count);
    }

    public function test_super_admin_passes_all_catalog_actions(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $superAdmin->assignRole('Super Admin');

        $product = Product::factory()->create();

        $this->assertTrue($superAdmin->can('viewAny', Product::class));
        $this->assertTrue($superAdmin->can('update', $product));
        $this->assertTrue($superAdmin->can('delete', $product));
        $this->assertTrue($superAdmin->can('publish', $product));
        $this->assertTrue($superAdmin->can('viewAny', CatalogCategory::class));
        $this->assertTrue($superAdmin->can('viewAny', Brand::class));
        $this->assertTrue($superAdmin->can('viewAny', ProductInquiry::class));
    }

    public function test_author_has_no_catalog_permissions(): void
    {
        $author = User::factory()->admin()->create();
        $author->assignRole('Author');

        $product = Product::factory()->create();
        $inquiry = ProductInquiry::factory()->create();

        $this->assertFalse($author->can('viewAny', Product::class));
        $this->assertFalse($author->can('update', $product));
        $this->assertFalse($author->can('viewAny', CatalogCategory::class));
        $this->assertFalse($author->can('viewAny', Brand::class));
        $this->assertFalse($author->can('view', $inquiry));
        $this->assertFalse($author->can('export', ProductInquiry::class));
    }

    public function test_editor_can_manage_inquiries_but_not_settings(): void
    {
        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $inquiry = ProductInquiry::factory()->create();

        $this->assertTrue($editor->can('view', $inquiry));
        $this->assertTrue($editor->can('update', $inquiry));
        $this->assertTrue($editor->can('assign', $inquiry));
        $this->assertTrue($editor->can('export', ProductInquiry::class));
        $this->assertFalse($editor->hasPermissionTo('catalog.settings.manage'));
    }
}
