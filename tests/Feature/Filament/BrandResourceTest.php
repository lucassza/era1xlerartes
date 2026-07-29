<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_editor_can_access_brands_index(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        $response = $this->actingAs($user)->get('/admin/brands');

        $response->assertOk();
    }

    public function test_author_is_blocked_from_brands_index(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Author');

        $response = $this->actingAs($user)->get('/admin/brands');

        $response->assertForbidden();
    }
}
