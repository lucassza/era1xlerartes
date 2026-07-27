<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_tag_resource_index(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        Tag::factory()->create();

        $response = $this->actingAs($user)->get('/admin/tags');

        $response->assertOk();
    }
}
