<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ContentSanitizer
{
    public function sanitize(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return $this->buildSanitizer()->sanitize($html);
    }

    private function buildSanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowElement('h1')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('h5')
            ->allowElement('h6')
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('hr')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('blockquote', ['cite'])
            ->allowElement('ul', ['class'])
            ->allowElement('ol', ['class', 'start', 'type'])
            ->allowElement('li')
            ->allowElement('code', ['class'])
            ->allowElement('pre', ['class'])
            ->allowElement('kbd')
            ->allowElement('samp')
            ->allowElement('var')
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'loading'])
            ->allowElement('figure')
            ->allowElement('figcaption')
            ->allowElement('a', ['href', 'title', 'name'])
            ->dropElement('script')
            ->dropElement('style')
            ->dropElement('iframe')
            ->dropElement('object')
            ->dropElement('embed')
            ->dropElement('applet')
            ->dropElement('form')
            ->dropElement('input')
            ->dropElement('textarea')
            ->dropElement('button')
            ->dropElement('select')
            ->dropElement('link')
            ->dropElement('meta')
            ->dropElement('base')
            ->dropElement('svg')
            ->dropElement('math')
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowMediaSchemes(['https', 'http']);

        return new HtmlSanitizer($config);
    }
}
