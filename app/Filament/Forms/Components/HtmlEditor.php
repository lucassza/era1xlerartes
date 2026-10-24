<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Services\ContentSanitizer;
use Filament\Forms\Components\Field;

class HtmlEditor extends Field
{
    protected string $view = 'filament.forms.components.html-editor';

    protected int $editorHeight = 360;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();

        $this->dehydrateStateUsing(
            static fn (?string $state): string => app(ContentSanitizer::class)->sanitize($state),
        );
    }

    public function editorHeight(int $pixels): static
    {
        $this->editorHeight = $pixels;

        return $this;
    }

    public function getEditorHeight(): int
    {
        return $this->editorHeight;
    }
}
