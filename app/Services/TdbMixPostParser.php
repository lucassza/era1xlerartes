<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\TdbMixPostData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TdbMixPostParser
{
    public function parse(string $html, string $sourceUrl): TdbMixPostData
    {
        $dom = new \DOMDocument;

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);

        $xpath = new \DOMXPath($dom);
        $postBodyNode = $xpath->query("//div[contains(@class, 'post-body')]")->item(0);
        $documentTitle = $this->normalizeDocumentTitle($this->firstNodeValue($xpath, '//title'));

        $title = $this->extractTitleFromPostBody($xpath, $postBodyNode)
            ?? $documentTitle
            ?? Str::headline($this->slugFromUrl($sourceUrl));

        $content = $postBodyNode instanceof \DOMNode ? $this->innerHtml($postBodyNode) : '';
        $content = $this->normalizeContent($content);

        $publishedAtRaw = $this->firstNodeAttribute($xpath, "//abbr[contains(@class, 'published')][1]", 'title');
        $publishedAt = $publishedAtRaw ? Carbon::parse($publishedAtRaw) : null;

        $tagNodes = $xpath->query("//span[contains(@class, 'post-labels')]//a[@rel='tag']");
        $tags = [];

        if ($tagNodes !== false) {
            foreach ($tagNodes as $tagNode) {
                $tag = trim($tagNode->textContent);

                if ($tag !== '') {
                    $tags[] = $tag;
                }
            }
        }

        $featuredImageUrl = $this->firstNodeAttribute($xpath, "//meta[@property='og:image'][1]", 'content')
            ?? $this->firstNodeAttribute($xpath, "//div[contains(@class, 'post-body')]//img[1]", 'src');

        $embeddedImageUrls = $this->extractEmbeddedImageUrls($xpath, $postBodyNode, $featuredImageUrl);

        return new TdbMixPostData(
            title: trim($title),
            slug: $this->slugFromUrl($sourceUrl),
            content: $content,
            excerpt: Str::of(strip_tags($content))->squish()->limit(240)->toString(),
            sourceUrl: $sourceUrl,
            authorName: config('import.tdbmix.default_author_name', 'TDBMix'),
            publishedAt: $publishedAt,
            tags: array_values(array_unique($tags)),
            featuredImageUrl: $featuredImageUrl,
            embeddedImageUrls: $embeddedImageUrls,
        );
    }

    private function innerHtml(\DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $childNode) {
            $html .= $node->ownerDocument?->saveHTML($childNode) ?? '';
        }

        return $html;
    }

    private function normalizeContent(string $content): string
    {
        $content = str_replace(['<o:p></o:p>', '<o:p>&nbsp;</o:p>'], '', $content);

        return trim($content);
    }

    private function extractTitleFromPostBody(\DOMXPath $xpath, ?\DOMNode $postBodyNode): ?string
    {
        if (! $postBodyNode instanceof \DOMNode) {
            return null;
        }

        $headingNodes = $xpath->query('.//h1[normalize-space()] | .//h2[normalize-space()] | .//h3[normalize-space()]', $postBodyNode);
        $heading = $headingNodes !== false ? $headingNodes->item(0) : null;

        if ($heading instanceof \DOMNode) {
            return $this->normalizeText($heading->textContent);
        }

        $paragraphs = $xpath->query('.//p', $postBodyNode);

        if ($paragraphs !== false) {
            foreach ($paragraphs as $paragraph) {
                $text = $this->normalizeText($paragraph->textContent);

                if ($text !== null && $this->looksLikeTitle($text)) {
                    return $text;
                }
            }
        }

        return null;
    }

    private function slugFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $segment = basename($path, '.html');

        return Str::slug($segment !== '' ? $segment : $url);
    }

    private function firstNodeValue(\DOMXPath $xpath, string $expression): ?string
    {
        $nodes = $xpath->query($expression);
        $node = $nodes !== false ? $nodes->item(0) : null;

        if (! $node instanceof \DOMNode) {
            return null;
        }

        return $this->normalizeText($node->textContent);
    }

    private function firstNodeAttribute(\DOMXPath $xpath, string $expression, string $attribute): ?string
    {
        $nodes = $xpath->query($expression);
        $node = $nodes !== false ? $nodes->item(0) : null;

        if (! $node instanceof \DOMElement) {
            return null;
        }

        $value = trim($node->getAttribute($attribute));

        return $value !== '' ? $value : null;
    }

    /**
     * @return array<int, string>
     */
    private function extractEmbeddedImageUrls(\DOMXPath $xpath, ?\DOMNode $postBodyNode, ?string $featuredImageUrl): array
    {
        if (! $postBodyNode instanceof \DOMNode) {
            return [];
        }

        $images = $xpath->query('.//img[@src]', $postBodyNode);

        if ($images === false) {
            return [];
        }

        $urls = [];

        foreach ($images as $image) {
            if (! $image instanceof \DOMElement) {
                continue;
            }

            $src = trim($image->getAttribute('src'));

            if ($src === '' || $src === $featuredImageUrl) {
                continue;
            }

            $urls[] = $src;
        }

        return array_values(array_unique($urls));
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace(["\xc2\xa0", "\xa0"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return $value !== '' ? $value : null;
    }

    private function normalizeDocumentTitle(?string $title): ?string
    {
        $title = $this->normalizeText($title);

        if ($title === null || Str::lower($title) === 'tdbmix') {
            return null;
        }

        $normalized = preg_replace('/^TDBMix\s*[:\-–—]\s*/u', '', $title) ?? $title;
        $normalized = $this->normalizeText($normalized);

        return $normalized !== null && Str::lower($normalized) !== 'tdbmix'
            ? $normalized
            : null;
    }

    private function looksLikeTitle(string $text): bool
    {
        $plain = Str::of($text)->squish();

        return $plain->length() <= 90 && str_word_count($plain->toString()) <= 12;
    }
}
