<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_editor_can_access_products_index(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        $response = $this->actingAs($user)->get('/admin/products');

        $response->assertOk();
    }

    public function test_author_is_blocked_from_products_index(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Author');

        $response = $this->actingAs($user)->get('/admin/products');

        $response->assertForbidden();
    }
}
