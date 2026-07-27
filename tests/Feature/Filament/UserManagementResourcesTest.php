<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_user_role_and_permission_indexes(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($superAdmin)->get('/admin/users')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/roles')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/permissions')->assertOk();
    }

    public function test_editor_cannot_access_user_management_indexes(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/roles')->assertForbidden();
        $this->actingAs($editor)->get('/admin/permissions')->assertForbidden();
    }
}
