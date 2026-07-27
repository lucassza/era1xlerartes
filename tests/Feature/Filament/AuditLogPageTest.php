<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_audit_log_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $this->actingAs($editor)->get('/admin/audit')->assertOk();
    }

    public function test_author_cannot_access_audit_log_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $author = User::factory()->admin()->create();
        $author->assignRole('Author');

        $this->actingAs($author)->get('/admin/audit')->assertForbidden();
    }
}
