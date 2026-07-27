<?php

declare(strict_types=1);

namespace App\Media;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class SlugMediaFileNamer extends FileNamer
{
    public function originalFileName(string $fileName): string
    {
        return Str::slug(pathinfo($fileName, PATHINFO_FILENAME));
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        return sprintf('%s-%s', $this->originalFileName($fileName), $conversion->getName());
    }

    public function responsiveFileName(string $fileName): string
    {
        return $this->originalFileName($fileName);
    }
}
