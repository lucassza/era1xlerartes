<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_renders(): void
    {
        $url = route('search.index');

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Busca');
        $response->assertSee('Buscar posts');
        $response->assertSee('Comece uma busca');
        $response->assertDontSee('Nenhum resultado encontrado');
    }

    public function test_search_page_shows_published_results_only(): void
    {
        $published = Post::factory()->published()->create(['title' => 'Laravel Search']);
        Post::factory()->create(['title' => 'Laravel Search Draft']);

        $response = $this->get(route('search.index', ['q' => 'Laravel']));

        $response->assertOk();
        $response->assertSee('Laravel Search');
        $response->assertDontSee('Laravel Search Draft');
        $response->assertDontSee('Nenhum resultado encontrado');
        $response->assertSee(route('posts.show', $published->slug), false);
        $response->assertSee('value="Laravel"', false);
    }

    public function test_search_page_handles_empty_results(): void
    {
        $query = 'nao-encontrado';

        $response = $this->get(route('search.index', ['q' => $query]));

        $response->assertOk();
        $response->assertSee('Nenhum resultado encontrado');
        $response->assertSee($query);
        $response->assertDontSee('Comece uma busca');
    }
}
