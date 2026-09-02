<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Post;
use Database\Seeders\BlogContentSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_content_seeder_creates_taxonomies_authors_and_fifty_posts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(BlogContentSeeder::class);

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('tags', 15);
        $this->assertGreaterThanOrEqual(4, Author::query()->count());
        $this->assertSame(50, Post::query()->whereNull('source_platform')->count());
        $this->assertSame(50, Post::query()
            ->whereNull('source_platform')
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'cover'))
            ->count());
        $this->assertGreaterThan(0, Post::query()->where('status', PostStatus::Published)->count());
        $this->assertGreaterThan(0, Post::query()->where('status', PostStatus::Scheduled)->count());
    }
}
