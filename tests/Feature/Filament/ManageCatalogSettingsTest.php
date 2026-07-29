<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageCatalogSettings;
use App\Models\CatalogSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageCatalogSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_only_authorized_users_can_open_catalog_settings(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $superAdmin->assignRole('Super Admin');

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $author = User::factory()->admin()->create();
        $author->assignRole('Author');

        $this->actingAs($superAdmin)->get('/admin/catalog-settings')->assertOk();
        $this->actingAs($editor)->get('/admin/catalog-settings')->assertForbidden();
        $this->actingAs($author)->get('/admin/catalog-settings')->assertForbidden();
    }

    public function test_super_admin_can_save_hero_configuration(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $superAdmin->assignRole('Super Admin');

        $payload = [
            'hero_enabled' => true,
            'hero_title' => 'Linha completa de produtos',
            'hero_subtitle' => 'Atendimento comercial em todo o Brasil.',
            'hero_cta_label' => 'Ver produtos',
            'hero_cta_url' => 'https://example.com/produtos',
            'hero_image_path' => null,
            'hero_alignment' => 'left',
            'title' => 'Catálogo',
            'intro_text' => 'Conheça nossos produtos.',
            'per_page' => 12,
            'default_sort' => 'recent',
            'show_unavailable' => true,
            'inquiry_button_text' => 'Consulte',
            'captcha_enabled' => false,
        ];

        $this->actingAs($superAdmin)
            ->get('/admin/catalog-settings')
            ->assertOk();

        Livewire::actingAs($superAdmin)
            ->test(ManageCatalogSettings::class)
            ->set('data', $payload)
            ->call('save')
            ->assertHasNoErrors();

        $settings = CatalogSetting::current();

        $this->assertTrue((bool) $settings->getAttribute('hero_enabled'));
        $this->assertSame('Linha completa de produtos', $settings->getAttribute('hero_title'));
        $this->assertSame('Atendimento comercial em todo o Brasil.', $settings->getAttribute('hero_subtitle'));
        $this->assertSame('Ver produtos', $settings->getAttribute('hero_cta_label'));
        $this->assertSame('https://example.com/produtos', $settings->getAttribute('hero_cta_url'));
        $this->assertSame('left', $settings->getAttribute('hero_alignment'));
    }
}
