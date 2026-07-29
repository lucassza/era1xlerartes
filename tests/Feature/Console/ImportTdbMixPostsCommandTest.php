<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTdbMixPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_can_run_in_dry_run_mode(): void
    {
        $image = UploadedFile::fake()->image('cover.jpg', 1200, 630);
        $validImage = file_get_contents($image->getRealPath());

        Http::fake([
            'https://www.tdbmix.com.br/sitemap.xml' => Http::response(
                "<?xml version='1.0'?><urlset><url><loc>https://www.tdbmix.com.br/2021/06/post-importado.html</loc></url></urlset>",
                200,
            ),
            'https://www.tdbmix.com.br/2021/06/post-importado.html' => Http::response(
                '<html><head><meta property="og:image" content="https://cdn.tdbmix.com.br/imagens/capa.jpg"></head><body><div class="post-body entry-content"><h1>Post Importado</h1><p>Conteúdo</p><img src="https://cdn.tdbmix.com.br/imagens/conteudo.jpg"></div><abbr class="published" title="2021-06-12T11:05:00-07:00"></abbr><span class="post-labels"><a rel="tag">TDBMix</a></span></body></html>',
                200,
            ),
            'https://cdn.tdbmix.com.br/imagens/conteudo.jpg' => Http::response($validImage, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('tdbmix:import-posts --dry-run')
            ->expectsOutput('[created] https://www.tdbmix.com.br/2021/06/post-importado.html')
            ->expectsOutput('1 post(s) processado(s).')
            ->assertSuccessful();

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_command_imports_post_from_sitemap(): void
    {
        $image = UploadedFile::fake()->image('cover.jpg', 1200, 630);
        $validImage = file_get_contents($image->getRealPath());

        Storage::fake('s3');

        config()->set('media-library.disk_name', 's3');
        config()->set('media-library.conversions_disk_name', 's3');
        config()->set('media-library.queue_conversions_by_default', false);

        Http::fake([
            'https://www.tdbmix.com.br/sitemap.xml' => Http::response(
                "<?xml version='1.0'?><urlset><url><loc>https://www.tdbmix.com.br/2021/06/post-importado.html</loc></url></urlset>",
                200,
            ),
            'https://www.tdbmix.com.br/2021/06/post-importado.html' => Http::response(
                '<html><head><meta property="og:image" content="https://cdn.tdbmix.com.br/imagens/capa.jpg"></head><body><div class="post-body entry-content"><h1>Post Importado</h1><p>Conteúdo importado do blogger.</p><img src="https://cdn.tdbmix.com.br/imagens/conteudo.jpg"></div><abbr class="published" title="2021-06-12T11:05:00-07:00"></abbr><span class="post-labels"><a rel="tag">TDBMix</a><a rel="tag">DIY</a></span></body></html>',
                200,
            ),
            'https://cdn.tdbmix.com.br/imagens/capa.jpg' => Http::response($validImage, 200, ['Content-Type' => 'image/jpeg']),
            'https://cdn.tdbmix.com.br/imagens/conteudo.jpg' => Http::response($validImage, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('tdbmix:import-posts')
            ->expectsOutput('[created] https://www.tdbmix.com.br/2021/06/post-importado.html')
            ->expectsOutput('1 post(s) processado(s).')
            ->assertSuccessful();

        $post = Post::query()->where('source_url', 'https://www.tdbmix.com.br/2021/06/post-importado.html')->first();

        $this->assertNotNull($post);
        $this->assertSame('Post Importado', $post->title);
        $this->assertSame('tdbmix-blogger', $post->source_platform);
        $this->assertTrue($post->tags->pluck('name')->contains('DIY'));
        $this->assertNotNull($post->getFirstMedia('cover'));
        $this->assertCount(1, $post->getMedia('body'));
        Storage::disk('s3')->assertExists($post->getFirstMedia('cover')->getPathRelativeToRoot());
        Storage::disk('s3')->assertExists($post->getMedia('body')->first()->getPathRelativeToRoot());
        $this->assertStringNotContainsString('https://cdn.tdbmix.com.br/imagens/conteudo.jpg', $post->fresh()->content);
        $this->assertStringContainsString(
            $post->getMedia('body')->first()->getPathRelativeToRoot(),
            $post->fresh()->content,
        );
    }
}
