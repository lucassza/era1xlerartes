<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\PostStatus;
use App\Models\User;
use App\Services\PostEditorialWorkflowService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostEditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_cannot_mark_post_as_published_without_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $authorUser = User::factory()->admin()->create();
        $authorUser->assignRole('Author');

        $this->expectException(AuthorizationException::class);

        app(PostEditorialWorkflowService::class)->guardAndNormalizePayload([
            'status' => PostStatus::Published->value,
            'published_at' => now(),
        ], $authorUser);
    }

    public function test_editor_can_schedule_with_future_publish_date(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $editor = User::factory()->admin()->create();
        $editor->assignRole('Editor');

        $data = app(PostEditorialWorkflowService::class)->guardAndNormalizePayload([
            'status' => PostStatus::Scheduled->value,
            'published_at' => now()->addHour(),
        ], $editor);

        $this->assertSame(PostStatus::Scheduled->value, $data['status']);
        $this->assertNotNull($data['published_at']);
    }
}
