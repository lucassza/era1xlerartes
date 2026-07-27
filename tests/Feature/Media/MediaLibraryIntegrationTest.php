<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_cover_is_stored_on_s3_disk_with_blog_path_prefix(): void
    {
        Storage::fake('s3');

        config()->set('media-library.disk_name', 's3');
        config()->set('media-library.conversions_disk_name', 's3');

        $post = Post::factory()->create();

        $post
            ->addMedia(UploadedFile::fake()->image('cover.jpg', 1600, 900))
            ->usingName('Capa')
            ->toMediaCollection('cover');

        $media = $post->getFirstMedia('cover');

        $this->assertNotNull($media);
        $this->assertStringStartsWith("blog/posts/{$post->id}/", $media->getPathRelativeToRoot());
        Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_category_and_author_register_expected_media_collections(): void
    {
        $category = Category::factory()->create();
        $author = Author::factory()->create();

        $this->assertSame('image', $category->getRegisteredMediaCollections()->first()->name);
        $this->assertSame('avatar', $author->getRegisteredMediaCollections()->first()->name);
    }
}
