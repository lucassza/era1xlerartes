<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_generates_slug_and_sanitizes_content(): void
    {
        $post = Post::factory()->create([
            'title' => 'Meu Post Incrível',
            'slug' => '',
            'content' => '<script>alert(1)</script><p>Conteúdo</p>',
        ]);

        $this->assertSame('meu-post-incrivel', $post->slug);
        $this->assertStringNotContainsString('<script>', $post->content);
        $this->assertStringContainsString('Conteúdo', $post->content);
    }

    public function test_post_supports_editorial_states(): void
    {
        $draft = Post::factory()->create(['status' => PostStatus::Draft]);
        $scheduled = Post::factory()->scheduled()->create();
        $published = Post::factory()->published()->create();
        $archived = Post::factory()->archived()->create();

        $this->assertSame(PostStatus::Draft, $draft->status);
        $this->assertSame(PostStatus::Scheduled, $scheduled->status);
        $this->assertNotNull($scheduled->published_at);
        $this->assertSame(PostStatus::Published, $published->status);
        $this->assertNotNull($published->published_at);
        $this->assertSame(PostStatus::Archived, $archived->status);
        $this->assertNotNull($archived->archived_at);
    }

    public function test_post_can_attach_tags(): void
    {
        $post = Post::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $post->tags()->attach($tags->pluck('id'));

        $this->assertCount(2, $post->fresh()->tags);
    }

    public function test_post_slug_must_be_unique(): void
    {
        Post::factory()->create(['slug' => 'slug-unico']);

        $this->expectException(QueryException::class);

        Post::factory()->create(['slug' => 'slug-unico']);
    }
}
