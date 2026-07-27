<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ProductInquiryAdminNotification;
use App\Mail\ProductInquiryVisitorConfirmation;
use App\Models\ProductInquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InquiryNotificationService
{
    public static function dispatch(ProductInquiry $inquiry): void
    {
        $inquiry->loadMissing(['product', 'assignee']);

        $recipients = static::resolveRecipients();

        try {
            if (! empty($recipients)) {
                Mail::to($recipients)
                    ->queue(new ProductInquiryAdminNotification($inquiry));
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao enfileirar e-mail de consulta para admins', [
                'inquiry_id' => $inquiry->getKey(),
                'error' => $e->getMessage(),
            ]);
        }

        try {
            if (filled($inquiry->getAttribute('email'))) {
                Mail::to((string) $inquiry->getAttribute('email'))
                    ->queue(new ProductInquiryVisitorConfirmation($inquiry));
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao enfileirar e-mail de confirmação para visitante', [
                'inquiry_id' => $inquiry->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    public static function resolveRecipients(): array
    {
        $settings = app(CatalogCacheService::class)->settings();
        $raw = (string) $settings->getAttribute('inquiry_recipients');

        if (! filled($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', $raw) ?: [])));
    }
}
