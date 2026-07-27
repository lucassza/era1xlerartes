<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageCatalogSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_only_authorized_users_can_open_catalog_settings(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $superAdmin->assignRole('Super Admin');

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $author = User::factory()->admin()->create();
        $author->assignRole('Author');

        $this->actingAs($superAdmin)->get('/admin/catalog-settings')->assertOk();
        $this->actingAs($editor)->get('/admin/catalog-settings')->assertForbidden();
        $this->actingAs($author)->get('/admin/catalog-settings')->assertForbidden();
    }
}
