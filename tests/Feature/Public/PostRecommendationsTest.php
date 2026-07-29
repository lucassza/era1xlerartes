<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PostRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_page_shows_related_posts_by_shared_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $post = Post::factory()->published()->create(['title' => 'Post principal']);
        $related = Post::factory()->published()->create(['title' => 'Relacionado']);
        $other = Post::factory()->published()->create(['title' => 'Outro']);

        $post->tags()->attach($tag);
        $related->tags()->attach($tag);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee('Trilha de navegação');
        $response->assertSee('Compartilhar');
        $response->assertSee('Sobre o autor');
        $response->assertSee('Posts relacionados');
        $response->assertSee('Relacionado');
        $response->assertSee(route('posts.show', $related->slug), false);
        $response->assertViewHas('post', fn (Post $article): bool => $this->hasArticleRelationsLoaded($article));
        $response->assertViewHas('relatedPosts', fn (Collection $articles): bool => $articles->every(
            fn (Post $article): bool => $this->hasArticleRelationsLoaded($article),
        ));
    }

    public function test_post_page_shows_popular_posts(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Atual']);
        $popular = Post::factory()->published()->create(['title' => 'Popular', 'views_count' => 15]);
        Post::factory()->published()->create(['title' => 'Menos popular', 'views_count' => 3]);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertSee('Posts populares');
        $response->assertSee('Popular');
        $response->assertViewHas('popularPosts', fn (Collection $articles): bool => $articles->every(
            fn (Post $article): bool => $this->hasArticleRelationsLoaded($article),
        ));
    }

    public function test_post_view_increments_views_count(): void
    {
        $post = Post::factory()->published()->create(['views_count' => 0]);

        $this->get(route('posts.show', $post->slug))->assertOk();

        $this->assertSame(1, $post->fresh()->views_count);
    }

    private function hasArticleRelationsLoaded(Post $article): bool
    {
        return $article->relationLoaded('author')
            && $article->author->relationLoaded('user')
            && $article->relationLoaded('category')
            && $article->relationLoaded('tags');
    }
}
