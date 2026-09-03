<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageSettings;
use App\Models\BlogSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageFooterSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_can_edit_the_text_below_the_footer_logo(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $superAdmin->assignRole('Super Admin');

        Livewire::actingAs($superAdmin)
            ->test(ManageSettings::class)
            ->fillForm([
                'site_name' => 'Era1x Ler Artes',
                'footer_text' => 'Novo texto do rodapé sob a logo.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Novo texto do rodapé sob a logo.', BlogSetting::query()->value('footer_text'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Novo texto do rodapé sob a logo.', false);
    }

    public function test_blank_footer_text_is_hidden_from_the_site(): void
    {
        BlogSetting::query()->create([
            'site_name' => 'Era1x Ler Artes',
            'footer_text' => '',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee(BlogSetting::DEFAULT_FOOTER_TEXT, false);
    }

    public function test_default_text_is_used_when_no_settings_row_exists(): void
    {
        $this->assertSame(0, BlogSetting::query()->count());

        $this->get('/')
            ->assertOk()
            ->assertSee(BlogSetting::DEFAULT_FOOTER_TEXT, false);
    }
}
