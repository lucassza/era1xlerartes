<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\BlogSetting;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_successful(): void
    {
        $publishedPosts = Post::factory()->count(13)->published()->create();
        Post::factory()->create(['title' => 'Rascunho não publicado']);
        Post::factory()->scheduled()->create(['title' => 'Agendado não publicado']);
        BlogSetting::query()->create([
            'site_name' => 'TDB Mix Blog',
            'newsletter_enabled' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertViewHas('heroPost', fn (?Post $post): bool => $post instanceof Post
            && $post->status === PostStatus::Published
            && $this->hasEditorialRelationsLoaded($post),
        );
        $response->assertViewHas('featuredPosts', fn (Collection $posts): bool => $posts->count() === 2
            && $posts->every(fn (Post $post): bool => $post->status === PostStatus::Published
                && $this->hasEditorialRelationsLoaded($post)),
        );
        $response->assertViewHas('listingPosts', fn (Collection $posts): bool => $posts->count() === 9
            && $posts->every(fn (Post $post): bool => $post->status === PostStatus::Published
                && $this->hasEditorialRelationsLoaded($post)),
        );
        $response->assertViewHas('recentPosts', fn (Collection $posts): bool => $posts->count() === 12
            && $posts->pluck('id')->diff($publishedPosts->pluck('id'))->isEmpty()
            && $posts->every(fn (Post $post): bool => $this->hasEditorialRelationsLoaded($post)),
        );
        $response->assertViewHas('popularPosts', fn (Collection $posts): bool => $posts->count() <= 5
            && $posts->every(
                fn (Post $post): bool => $post->status === PostStatus::Published
                    && $post->relationLoaded('author')
                    && $post->relationLoaded('category'),
            ));
        $response->assertViewHas('newsletterEnabled', false);
        $response->assertViewHas('categoryTopics');
        $response->assertSee('id="destaques"', false);
        $response->assertSee('id="posts-recentes"', false);
        $response->assertSee('id="populares"', false);
    }

    public function test_home_page_provides_landmarks_skip_link_and_primary_navigation(): void
    {
        BlogSetting::query()->create([
            'site_name' => 'TDB Mix Blog',
            'newsletter_enabled' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('Pular para o conteúdo principal');
        $response->assertSee('<header', false);
        $response->assertSee('<main id="main-content" tabindex="-1">', false);
        $response->assertSee('<footer', false);
        $response->assertSee('aria-label="Navegação principal"', false);
        $response->assertSee('aria-label="Navegação móvel"', false);
        $response->assertSee('aria-label="Links do rodapé"', false);
        $response->assertSee('href="'.route('home').'"', false);
        $response->assertSee('href="'.route('search.index').'"', false);
        self::assertSame(1, substr_count($response->getContent(), 'id="main-content"'));
    }

    public function test_home_page_renders_named_empty_states_without_unpublished_content(): void
    {
        BlogSetting::query()->create([
            'site_name' => 'TDB Mix Blog',
            'newsletter_enabled' => false,
        ]);
        Post::factory()->create(['title' => 'Rascunho oculto']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sem destaques por enquanto');
        $response->assertSee('Ainda não há posts recentes');
        $response->assertSee('Sem ranking ainda');
        $response->assertDontSee('Rascunho oculto');
    }

    private function hasEditorialRelationsLoaded(Post $post): bool
    {
        return $post->relationLoaded('author')
            && $post->relationLoaded('category')
            && $post->relationLoaded('tags');
    }
}
