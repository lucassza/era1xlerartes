<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductAvailability: string
{
    case Available = 'available';
    case OnDemand = 'on_demand';
    case Discontinued = 'discontinued';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponível',
            self::OnDemand => 'Sob encomenda',
            self::Discontinued => 'Descontinuado',
        };
    }
}
