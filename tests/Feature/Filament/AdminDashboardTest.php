<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_filament_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('Meu perfil');
        $response->assertSee(route('filament.admin.auth.profile'), false);
        $response->assertDontSee(route('site.profile.edit'), false);

        $response = $this->actingAs($user)
            ->get(route('filament.admin.auth.profile'))
            ->assertOk();

        $response->assertSee('Avatar');
    }

    public function test_super_admin_can_access_admin_profile_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $response = $this->actingAs($user)->get('/admin/profile');

        $response->assertOk();
        $response->assertSee('Avatar');
    }
}
