<?php

declare(strict_types=1);

namespace App\Enums;

enum PriceDisplayMode: string
{
    case Show = 'show';
    case OnRequest = 'on_request';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Show => 'Exibir preço',
            self::OnRequest => 'Sob consulta',
            self::Hidden => 'Ocultar',
        };
    }
}
