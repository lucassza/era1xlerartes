<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishScheduledPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_publishes_due_scheduled_posts(): void
    {
        $post = Post::factory()->scheduled()->create([
            'published_at' => now()->subMinute(),
        ]);

        $this->artisan('posts:publish-scheduled')
            ->expectsOutput('1 post(s) publicado(s).')
            ->assertSuccessful();

        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_command_keeps_future_scheduled_posts_unpublished(): void
    {
        $post = Post::factory()->scheduled()->create([
            'published_at' => now()->addHour(),
        ]);

        $this->artisan('posts:publish-scheduled')
            ->expectsOutput('0 post(s) publicado(s).')
            ->assertSuccessful();

        $this->assertSame(PostStatus::Scheduled, $post->fresh()->status);
    }
}
