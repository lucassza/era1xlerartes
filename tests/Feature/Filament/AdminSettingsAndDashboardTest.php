<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\BlogSetting;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_settings_page_and_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        BlogSetting::query()->create([
            'site_name' => 'Era1x Ler Artes',
            'newsletter_enabled' => true,
        ]);

        Post::factory()->count(2)->published()->create();

        $superAdmin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($superAdmin)->get('/admin')->assertOk();
        $this->actingAs($superAdmin)->get('/admin/settings')->assertOk();
    }

    public function test_editor_cannot_access_settings_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
    }
}
