<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\CatalogCategory;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guard_against_self_reference(): void
    {
        $category = CatalogCategory::factory()->create();

        $this->expectException(\DomainException::class);
        $category->parent_id = $category->id;
        $category->save();
    }

    public function test_guard_against_cycle_through_descendant(): void
    {
        $root = CatalogCategory::factory()->create();
        $child = CatalogCategory::factory()->create(['parent_id' => $root->id]);

        $this->expectException(\DomainException::class);
        $root->parent_id = $child->id;
        $root->save();
    }

    public function test_descendant_ids_includes_subtree(): void
    {
        $root = CatalogCategory::factory()->create();
        $child = CatalogCategory::factory()->create(['parent_id' => $root->id]);
        $grandchild = CatalogCategory::factory()->create(['parent_id' => $child->id]);

        $ids = $root->descendantIds()->all();

        $this->assertContains($child->id, $ids);
        $this->assertContains($grandchild->id, $ids);
        $this->assertNotContains($root->id, $ids);
    }
}
