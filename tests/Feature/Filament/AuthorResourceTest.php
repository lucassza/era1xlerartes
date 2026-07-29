<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Author;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_author_resource_index(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        Author::factory()->create();

        $response = $this->actingAs($user)->get('/admin/authors');

        $response->assertOk();
    }

    public function test_author_update_is_logged(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $author = Author::factory()->create(['name' => 'Autor Original']);

        $this->actingAs($editor);

        $author->update(['name' => 'Autor Atualizado']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'authors',
            'event' => 'updated',
            'subject_type' => Author::class,
            'subject_id' => $author->id,
        ]);
    }
}
