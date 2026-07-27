<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TdbMixPostParser;
use Tests\TestCase;

class TdbMixPostParserTest extends TestCase
{
    public function test_parser_extracts_post_fields_from_blogger_html(): void
    {
        $html = <<<'HTML'
        <html>
            <head>
                <meta property="og:image" content="https://example.com/image.jpg">
            </head>
            <body>
                <div class="post-body entry-content">
                    <h1>Título Importado</h1>
                    <p>Conteúdo principal do post.</p>
                </div>
                <abbr class="published" title="2021-06-12T11:05:00-07:00">11:05:00</abbr>
                <span class="post-labels">
                    <a rel="tag">TDBMix</a>
                    <a rel="tag">casamento</a>
                </span>
            </body>
        </html>
        HTML;

        $data = app(TdbMixPostParser::class)->parse($html, 'https://www.tdbmix.com.br/2021/06/projeto-wedding-tdbmix-organizar.html');

        $this->assertSame('Título Importado', $data->title);
        $this->assertSame('projeto-wedding-tdbmix-organizar', $data->slug);
        $this->assertStringContainsString('Conteúdo principal do post.', $data->content);
        $this->assertSame('https://example.com/image.jpg', $data->featuredImageUrl);
        $this->assertSame(['TDBMix', 'casamento'], $data->tags);
        $this->assertSame([], $data->embeddedImageUrls);
        $this->assertNotNull($data->publishedAt);
    }

    public function test_parser_extracts_title_from_first_meaningful_paragraph_when_heading_is_missing(): void
    {
        $html = <<<'HTML'
        <html>
            <head>
                <title>TDBMix</title>
            </head>
            <body>
                <div class="post-body entry-content">
                    <p>&nbsp;</p>
                    <div>
                        <p>Projeto Wedding TDBMix</p>
                    </div>
                    <p>Corpo do conteúdo importado.</p>
                </div>
                <abbr class="published" title="2021-06-12T11:05:00-07:00"></abbr>
            </body>
        </html>
        HTML;

        $data = app(TdbMixPostParser::class)->parse($html, 'https://www.tdbmix.com.br/2021/06/projeto-wedding-tdbmix-organizar.html');

        $this->assertSame('Projeto Wedding TDBMix', $data->title);
    }

    public function test_parser_extracts_embedded_images_excluding_featured_image(): void
    {
        $html = <<<'HTML'
        <html>
            <head>
                <meta property="og:image" content="https://example.com/featured.jpg">
            </head>
            <body>
                <div class="post-body entry-content">
                    <h1>Post com imagens</h1>
                    <img src="https://example.com/featured.jpg" />
                    <p>Conteúdo</p>
                    <img src="https://example.com/inline-1.jpg" />
                    <img src="https://example.com/inline-2.jpg" />
                </div>
            </body>
        </html>
        HTML;

        $data = app(TdbMixPostParser::class)->parse($html, 'https://www.tdbmix.com.br/2021/06/post-com-imagens.html');

        $this->assertSame('https://example.com/featured.jpg', $data->featuredImageUrl);
        $this->assertSame([
            'https://example.com/inline-1.jpg',
            'https://example.com/inline-2.jpg',
        ], $data->embeddedImageUrls);
    }

    public function test_parser_uses_document_title_when_it_contains_specific_post_title(): void
    {
        $html = <<<'HTML'
        <html>
            <head>
                <title>TDBMix: O que aconteceu com a Angel?</title>
            </head>
            <body>
                <div class="post-body entry-content">
                    <p>Texto longo demais para ser um título de post importado com segurança pelo parser atual.</p>
                </div>
            </body>
        </html>
        HTML;

        $data = app(TdbMixPostParser::class)->parse($html, 'https://www.tdbmix.com.br/2019/10/o-que-aconteceu-com-angel.html');

        $this->assertSame('O que aconteceu com a Angel?', $data->title);
    }

    public function test_parser_falls_back_to_slug_when_only_generic_title_and_body_text_are_too_long(): void
    {
        $html = <<<'HTML'
        <html>
            <head>
                <title>TDBMix</title>
            </head>
            <body>
                <div class="post-body entry-content">
                    <p>Hoje, resolvi fazer uma postagem diferente! 10 anos equivalem a uma década e este texto é longo demais para ser usado como título automaticamente.</p>
                </div>
            </body>
        </html>
        HTML;

        $data = app(TdbMixPostParser::class)->parse($html, 'https://www.tdbmix.com.br/2019/10/4-decadas.html');

        $this->assertSame('4 Decadas', $data->title);
    }
}
