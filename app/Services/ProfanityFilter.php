<?php

declare(strict_types=1);

namespace App\Services;

class ProfanityFilter
{
    /**
     * @var array<int, string>|null
     */
    private ?array $terms = null;

    public function __construct(
        private readonly bool $enabled = true,
        private readonly array $configuredTerms = [],
        private readonly string $replacement = '*',
    ) {}

    public function containsProfanity(string $text): bool
    {
        return $this->firstMatch($text) !== null;
    }

    public function firstMatch(string $text): ?string
    {
        if (! $this->enabled) {
            return null;
        }

        $normalized = $this->normalize($text);

        foreach ($this->terms() as $term) {
            if ($this->matches($normalized, $term)) {
                return $term;
            }
        }

        return null;
    }

    public function censor(string $text): string
    {
        if (! $this->enabled) {
            return $text;
        }

        $normalized = $this->normalize($text);

        foreach ($this->terms() as $term) {
            $pattern = $this->buildPattern($term);
            $normalized = preg_replace_callback($pattern, function (array $matches): string {
                $length = mb_strlen($matches[0]);

                return $length > 2
                    ? mb_substr($matches[0], 0, 1).str_repeat($this->replacement, $length - 2).mb_substr($matches[0], -1)
                    : str_repeat($this->replacement, $length);
            }, $normalized) ?? $normalized;
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function terms(): array
    {
        if ($this->terms !== null) {
            return $this->terms;
        }

        $configured = $this->configuredTerms ?: (array) config('profanity.terms', []);

        $this->terms = array_values(array_unique(array_filter(array_map(
            fn (string $term): string => $this->normalize($term),
            $configured,
        ))));

        return $this->terms;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        return preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? $text;
    }

    private function matches(string $normalized, string $term): bool
    {
        return preg_match($this->buildPattern($term), $normalized) === 1;
    }

    private function buildPattern(string $term): string
    {
        $escaped = preg_quote($term, '/');

        return '/(?<![\p{L}\p{N}])'.$escaped.'(?![\p{L}\p{N}])/u';
    }
}
