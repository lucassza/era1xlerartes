<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Exceptions\LastSuperAdminException;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminSecurityService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LastSuperAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_remove_super_admin_role_from_last_super_admin(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $service = app(AdminSecurityService::class);
        $superAdmin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->expectException(LastSuperAdminException::class);

        $service->syncUserRoles($superAdmin, ['Editor'], $superAdmin);
    }

    public function test_can_remove_super_admin_role_if_another_super_admin_exists(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $service = app(AdminSecurityService::class);
        $first = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $second = User::factory()->create();
        $second->assignRole('Super Admin');

        $service->syncUserRoles($first, ['Editor'], $second);

        $this->assertFalse($first->fresh()->hasRole('Super Admin'));
        $this->assertTrue($first->fresh()->hasRole('Editor'));
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'admin-security',
            'event' => 'roles_synced',
            'subject_type' => User::class,
            'subject_id' => $first->id,
        ]);
    }

    public function test_cannot_delete_last_super_admin_user(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $service = app(AdminSecurityService::class);
        $superAdmin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->expectException(LastSuperAdminException::class);

        $service->deleteUser($superAdmin, $superAdmin);
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $service = app(AdminSecurityService::class);
        $superAdmin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $role = Role::query()->where('name', 'Super Admin')->firstOrFail();

        $this->expectException(LastSuperAdminException::class);

        $service->deleteRole($role, $superAdmin);
    }
}
