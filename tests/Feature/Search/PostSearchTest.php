<?php

declare(strict_types=1);

namespace Tests\Feature\Search;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\PostSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_text_search_finds_published_post(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'PostgreSQL avançado para blogs',
            'excerpt' => 'Busca com texto completo.',
            'content' => '<p>Implementando busca nativa em PostgreSQL.</p>',
        ]);

        Post::factory()->create([
            'title' => 'Rascunho oculto',
        ]);

        $results = app(PostSearchService::class)->searchPublished('PostgreSQL');

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($post));
    }

    public function test_trigram_search_finds_typo_in_title(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'Laravel Filament moderno',
        ]);

        $results = app(PostSearchService::class)->searchPublished('Laarvel');

        $this->assertTrue($results->contains(fn (Post $result): bool => $result->is($post)));
    }

    public function test_search_ignores_archived_and_scheduled_posts(): void
    {
        Post::factory()->archived()->create([
            'title' => 'Arquivo PostgreSQL',
            'status' => PostStatus::Archived,
        ]);

        Post::factory()->scheduled()->create([
            'title' => 'Agendado PostgreSQL',
        ]);

        $results = app(PostSearchService::class)->searchPublished('PostgreSQL');

        $this->assertCount(0, $results);
    }
}
