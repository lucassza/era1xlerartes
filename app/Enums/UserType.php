<?php

declare(strict_types=1);

namespace App\Enums;

enum UserType: string
{
    case Admin = 'admin';
    case SiteUser = 'site';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::SiteUser => 'Usuário do site',
        };
    }
}
