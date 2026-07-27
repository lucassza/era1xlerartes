<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Providers\Filament\AdminPanelProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class AdminBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_provider_defines_tdb_mix_colors(): void
    {
        $reflection = new ReflectionClass(AdminPanelProvider::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString("'primary' => Color::hex('#d42020')", (string) $source);
        $this->assertStringContainsString("'secondary' => Color::hex('#00bcd8')", (string) $source);
        $this->assertStringContainsString("'accent' => Color::hex('#e6287e')", (string) $source);
    }

    public function test_admin_panel_uses_tdb_mix_brand(): void
    {
        $reflection = new ReflectionClass(AdminPanelProvider::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString("->brandName('TDB Mix Blog')", (string) $source);
        $this->assertStringContainsString("->brandLogo(asset('images/brand/logo-peq.png'))", (string) $source);
        $this->assertStringContainsString('->darkMode(false)', (string) $source);
    }

    public function test_admin_panel_uses_vite_theme(): void
    {
        $reflection = new ReflectionClass(AdminPanelProvider::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString("->viteTheme('resources/css/filament/theme.css')", (string) $source);
    }

    public function test_admin_panel_groups_navigation(): void
    {
        $reflection = new ReflectionClass(AdminPanelProvider::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString("NavigationGroup::make('Conteúdo')", (string) $source);
        $this->assertStringContainsString("NavigationGroup::make('Comunidade')", (string) $source);
        $this->assertStringContainsString("NavigationGroup::make('Configurações')", (string) $source);
    }

    public function test_login_page_renders_brand_header_and_footer(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Painel administrativo');
        $response->assertSee('Entre com suas credenciais');
        $response->assertSee('TDB Mix Blog · Painel restrito');
        $response->assertSee(asset('images/brand/logo-peq.png'));
    }

    public function test_password_reset_page_renders_brand_header(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $response = $this->get('/admin/password-reset/request');

        $response->assertOk();
        $response->assertSee('Recuperar acesso');
        $response->assertSee('Informe seu e-mail para receber o link');
    }
}
