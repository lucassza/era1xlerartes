<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Illuminate\Support\Carbon;

final readonly class TdbMixPostData
{
    /**
     * @param  array<int, string>  $tags
     * @param  array<int, string>  $embeddedImageUrls
     */
    public function __construct(
        public string $title,
        public string $slug,
        public string $content,
        public string $excerpt,
        public string $sourceUrl,
        public string $authorName,
        public ?Carbon $publishedAt,
        public array $tags,
        public ?string $featuredImageUrl,
        public array $embeddedImageUrls,
    ) {}
}
