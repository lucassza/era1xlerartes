<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_seeder_creates_expected_foundation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Editor']);
        $this->assertDatabaseHas('roles', ['name' => 'Author']);
        $this->assertDatabaseHas('permissions', ['name' => 'admin.access']);
    }

    public function test_super_admin_receives_global_gate_bypass(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(Role::query()->where('name', 'Super Admin')->firstOrFail());

        $this->assertTrue($user->can('posts.publish'));
        $this->assertTrue($user->can('permissions.manage'));
    }
}
