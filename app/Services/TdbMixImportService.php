<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\TdbMixPostData;
use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TdbMixImportService
{
    public function __construct(
        private readonly TdbMixPostParser $postParser,
    ) {}

    /**
     * @return Collection<int, string>
     */
    public function fetchPostUrls(?int $limit = null): Collection
    {
        $response = Http::timeout(config('import.tdbmix.request_timeout', 20))
            ->get(config('import.tdbmix.sitemap_url'));

        $response->throw();

        preg_match_all('#<loc>(.*?)</loc>#', $response->body(), $matches);

        /** @var array<int, string> $urls */
        $urls = $matches[1];

        return collect($urls)
            ->filter(fn (string $url): bool => Str::endsWith($url, '.html'))
            ->map(fn (string $url): string => trim($url))
            ->take($limit)
            ->values();
    }

    public function fetchAndParse(string $url): TdbMixPostData
    {
        $response = Http::timeout(config('import.tdbmix.request_timeout', 20))->get($url);

        $response->throw();

        return $this->postParser->parse($response->body(), $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function importParsedPost(TdbMixPostData $postData, bool $dryRun = false): array
    {
        $existing = Post::query()->where('source_url', $postData->sourceUrl)->first();

        if ($dryRun) {
            return [
                'created' => $existing === null,
                'updated' => $existing !== null,
                'slug' => $postData->slug,
                'source_url' => $postData->sourceUrl,
            ];
        }

        return DB::transaction(function () use ($postData, $existing): array {
            $author = $this->resolveAuthor($postData->authorName);
            $category = $this->resolveDefaultCategory();

            $post = $existing ?? new Post;

            $post->fill([
                'author_id' => $author->getKey(),
                'category_id' => $category->getKey(),
                'title' => $postData->title,
                'slug' => $postData->slug,
                'excerpt' => $postData->excerpt,
                'content' => $postData->content,
                'status' => PostStatus::Published,
                'published_at' => $postData->publishedAt,
                'archived_at' => null,
                'source_url' => $postData->sourceUrl,
                'source_platform' => 'tdbmix-blogger',
            ]);

            $originalContent = $postData->content;

            $post->save();

            $tagIds = collect($postData->tags)
                ->map(function (string $tagName): string {
                    $slug = Str::slug($tagName);

                    return Tag::query()->firstOrCreate([
                        'slug' => $slug,
                    ], [
                        'name' => trim($tagName),
                        'description' => null,
                        'is_active' => true,
                    ])->getKey();
                })
                ->all();

            $post->tags()->sync($tagIds);

            $mediaImported = $this->importFeaturedImage($post, $postData->featuredImageUrl);
            $embeddedMediaImported = $this->importEmbeddedImages($post, $postData->embeddedImageUrls);

            $post->forceFill([
                'content' => $this->rewriteEmbeddedMediaUrls($post, $originalContent, $postData->featuredImageUrl, $postData->embeddedImageUrls),
            ])->save();

            return [
                'created' => $existing === null,
                'updated' => $existing !== null,
                'post_id' => $post->getKey(),
                'slug' => $post->slug,
                'source_url' => $post->source_url,
                'media_imported' => $mediaImported,
                'embedded_media_imported' => $embeddedMediaImported,
            ];
        });
    }

    /**
     * @param  array<int, string>  $embeddedImageUrls
     */
    private function rewriteEmbeddedMediaUrls(Post $post, string $content, ?string $featuredImageUrl, array $embeddedImageUrls): string
    {
        if (trim($content) === '') {
            return $content;
        }

        $map = [];

        $coverMedia = $post->getFirstMedia('cover');

        if ($featuredImageUrl !== null && $coverMedia !== null) {
            $map[$featuredImageUrl] = URL::to($coverMedia->getUrl());
        }

        $bodyMedia = $post->getMedia('body')->values();

        foreach ($embeddedImageUrls as $index => $embeddedImageUrl) {
            $media = $bodyMedia->get($index);

            if ($media !== null) {
                $map[$embeddedImageUrl] = URL::to($media->getUrl());
            }
        }

        if ($map === []) {
            return $content;
        }

        $dom = new \DOMDocument;
        $html = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="import-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($dom);
        $images = $xpath->query('//div[@id="import-root"]//img[@src]');

        if ($images === false) {
            return $content;
        }

        foreach ($images as $image) {
            if (! $image instanceof \DOMElement) {
                continue;
            }

            $src = trim($image->getAttribute('src'));

            if (isset($map[$src])) {
                $image->setAttribute('src', $map[$src]);
            }
        }

        $root = $xpath->query('//div[@id="import-root"]')->item(0);

        if (! $root instanceof \DOMNode) {
            return $content;
        }

        $rewritten = '';

        foreach ($root->childNodes as $childNode) {
            $rewritten .= $dom->saveHTML($childNode) ?: '';
        }

        return $rewritten;
    }

    private function importFeaturedImage(Post $post, ?string $featuredImageUrl): bool
    {
        if (blank($featuredImageUrl)) {
            return false;
        }

        try {
            $response = Http::timeout(config('import.tdbmix.request_timeout', 20))->get($featuredImageUrl);
            $response->throw();
        } catch (\Throwable) {
            return false;
        }

        $path = parse_url($featuredImageUrl, PHP_URL_PATH) ?: 'cover.jpg';
        $fileName = basename($path) !== '' ? basename($path) : 'cover.jpg';
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($extension === '') {
            $mime = $response->header('Content-Type');
            $extension = match (true) {
                str_contains($mime, 'png') => 'png',
                str_contains($mime, 'webp') => 'webp',
                default => 'jpg',
            };
            $fileName .= '.'.$extension;
        }

        $temporaryBase = tempnam(sys_get_temp_dir(), 'tdbmix-import-');

        if ($temporaryBase === false) {
            return false;
        }

        $temporaryFile = $temporaryBase.'.'.$extension;

        @rename($temporaryBase, $temporaryFile);

        file_put_contents($temporaryFile, $response->body());

        try {
            $post
                ->addMedia($temporaryFile)
                ->usingName($post->title)
                ->usingFileName($fileName)
                ->toMediaCollection('cover');

            return true;
        } finally {
            @unlink($temporaryFile);
        }
    }

    /**
     * @param  array<int, string>  $embeddedImageUrls
     */
    private function importEmbeddedImages(Post $post, array $embeddedImageUrls): int
    {
        $imported = 0;

        foreach ($embeddedImageUrls as $embeddedImageUrl) {
            if ($this->importRemoteImageToCollection($post, $embeddedImageUrl, 'body')) {
                $imported++;
            }
        }

        return $imported;
    }

    private function importRemoteImageToCollection(Post $post, string $imageUrl, string $collection): bool
    {
        if (blank($imageUrl)) {
            return false;
        }

        try {
            $response = Http::timeout(config('import.tdbmix.request_timeout', 20))->get($imageUrl);
            $response->throw();
        } catch (\Throwable) {
            return false;
        }

        $path = parse_url($imageUrl, PHP_URL_PATH) ?: 'image.jpg';
        $fileName = basename($path) !== '' ? basename($path) : 'image.jpg';
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($extension === '') {
            $mime = $response->header('Content-Type');
            $extension = match (true) {
                str_contains($mime, 'png') => 'png',
                str_contains($mime, 'webp') => 'webp',
                default => 'jpg',
            };
            $fileName .= '.'.$extension;
        }

        $temporaryBase = tempnam(sys_get_temp_dir(), 'tdbmix-import-');

        if ($temporaryBase === false) {
            return false;
        }

        $temporaryFile = $temporaryBase.'.'.$extension;

        @rename($temporaryBase, $temporaryFile);
        file_put_contents($temporaryFile, $response->body());

        try {
            $post
                ->addMedia($temporaryFile)
                ->usingName($post->title)
                ->usingFileName($fileName)
                ->toMediaCollection($collection);

            return true;
        } finally {
            @unlink($temporaryFile);
        }
    }

    private function resolveAuthor(string $authorName): Author
    {
        $slug = Str::slug($authorName);
        $email = sprintf('%s@import.tdbmix.local', $slug !== '' ? $slug : 'tdbmix');

        $user = User::query()->firstOrCreate([
            'email' => $email,
        ], [
            'name' => $authorName,
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ]);

        return Author::query()->firstOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'name' => $authorName,
            'slug' => $slug !== '' ? $slug : Str::slug($email),
            'bio' => null,
            'is_active' => true,
        ]);
    }

    private function resolveDefaultCategory(): Category
    {
        $name = (string) config('import.tdbmix.default_category_name', 'Importados');

        return Category::query()->firstOrCreate([
            'name' => $name,
        ], [
            'slug' => Str::slug($name),
            'description' => 'Conteúdo importado automaticamente do TDBMix.',
            'is_active' => true,
        ]);
    }
}
