<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->siteUser()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('site.login'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.login.store'), [
                '_token' => 'test-token',
                'email' => 'login@example.com',
                'password' => 'password',
            ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_password_fails(): void
    {
        User::factory()->siteUser()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('site.login'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.login.store'), [
                '_token' => 'test-token',
                'email' => 'login@example.com',
                'password' => 'errada',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_banned_user_cannot_login(): void
    {
        User::factory()->siteUser()->banned()->create([
            'email' => 'banido@example.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('site.login'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('site.login.store'), [
                '_token' => 'test-token',
                'email' => 'banido@example.com',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_site_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->siteUser()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_user_can_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create([
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_admin_user_is_distinguished_by_type(): void
    {
        $admin = User::factory()->admin()->create();
        $site = User::factory()->siteUser()->create();

        $this->assertSame(UserType::Admin, $admin->type);
        $this->assertSame(UserType::SiteUser, $site->type);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($site->isSiteUser());
    }
}
