<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_post_resource_index(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        Post::factory()->create();

        $response = $this->actingAs($user)->get('/admin/posts');

        $response->assertOk();
    }

    public function test_post_update_is_logged(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $post = Post::factory()->create(['title' => 'Título inicial']);

        $this->actingAs($editor);

        $post->update(['title' => 'Título atualizado']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'posts',
            'event' => 'updated',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
        ]);
    }
}
