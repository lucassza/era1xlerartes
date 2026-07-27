<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Unavailable = 'unavailable';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Published => 'Publicado',
            self::Unavailable => 'Indisponível',
            self::Archived => 'Arquivado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Unavailable => 'warning',
            self::Archived => 'danger',
        };
    }
}
