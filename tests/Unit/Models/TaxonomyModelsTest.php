<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Author;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxonomyModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_generates_slug_when_missing(): void
    {
        $category = Category::query()->create([
            'name' => 'Tecnologia Web',
        ]);

        $this->assertSame('tecnologia-web', $category->slug);
    }

    public function test_tag_generates_slug_when_missing(): void
    {
        $tag = Tag::query()->create([
            'name' => 'Laravel Avançado',
        ]);

        $this->assertSame('laravel-avancado', $tag->slug);
    }

    public function test_author_belongs_to_user_and_user_has_author(): void
    {
        $author = Author::factory()->create();

        $this->assertInstanceOf(User::class, $author->user);
        $this->assertTrue($author->user->author()->exists());
        $this->assertSame($author->id, $author->user->author?->id);
    }

    public function test_category_slug_must_be_unique(): void
    {
        Category::factory()->create([
            'name' => 'Backend',
            'slug' => 'backend',
        ]);

        $this->expectException(QueryException::class);

        Category::factory()->create([
            'name' => 'Backend Duplicado',
            'slug' => 'backend',
        ]);
    }

    public function test_author_user_id_must_be_unique(): void
    {
        $user = User::factory()->create();

        Author::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->expectException(QueryException::class);

        Author::factory()->create([
            'user_id' => $user->id,
        ]);
    }
}
