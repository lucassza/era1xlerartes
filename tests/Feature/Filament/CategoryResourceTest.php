<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_category_resource_index(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        Category::factory()->create();

        $response = $this->actingAs($user)->get('/admin/categories');

        $response->assertOk();
    }
}
