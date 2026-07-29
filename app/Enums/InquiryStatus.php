<?php

declare(strict_types=1);

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Viewed = 'viewed';
    case InService = 'in_service';
    case WaitingCustomer = 'waiting_customer';
    case Answered = 'answered';
    case Completed = 'completed';
    case Discarded = 'discarded';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nova',
            self::Viewed => 'Visualizada',
            self::InService => 'Em atendimento',
            self::WaitingCustomer => 'Aguardando cliente',
            self::Answered => 'Respondida',
            self::Completed => 'Concluída',
            self::Discarded => 'Descartada',
            self::Spam => 'Spam',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Viewed => 'gray',
            self::InService => 'warning',
            self::WaitingCustomer => 'warning',
            self::Answered => 'success',
            self::Completed => 'success',
            self::Discarded => 'danger',
            self::Spam => 'danger',
        };
    }

    public function isOpen(): bool
    {
        return match ($this) {
            self::New, self::Viewed, self::InService, self::WaitingCustomer, self::Answered => true,
            default => false,
        };
    }
}
