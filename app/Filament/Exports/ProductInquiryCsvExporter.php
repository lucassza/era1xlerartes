<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\InquiryStatus;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductInquiryCsvExporter
{
    public static function stream(iterable $inquiries): StreamedResponse
    {
        $filename = 'consultas-'.now()->format('Ymd-His').'.csv';
        $columns = [
            'id',
            'product_name',
            'product_slug',
            'name',
            'email',
            'phone',
            'company',
            'city',
            'state',
            'subject',
            'message',
            'origin',
            'source_url',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'ip',
            'user_agent',
            'status',
            'assignee',
            'first_viewed_at',
            'first_answered_at',
            'completed_at',
            'created_at',
        ];

        return response()->streamDownload(function () use ($inquiries, $columns): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns, ';');

            foreach ($inquiries as $inquiry) {
                if (! $inquiry instanceof ProductInquiry) {
                    continue;
                }

                $inquiry->loadMissing('product', 'assignee');
                $product = $inquiry->product;
                $assignee = $inquiry->assignee;
                $utm = is_array($inquiry->getAttribute('utm')) ? $inquiry->getAttribute('utm') : [];
                $status = $inquiry->getAttribute('status');

                fputcsv($output, [
                    $inquiry->getKey(),
                    $inquiry->getAttribute('product_name'),
                    $product instanceof Product ? $product->getAttribute('slug') : null,
                    $inquiry->getAttribute('name'),
                    $inquiry->getAttribute('email'),
                    $inquiry->getAttribute('phone'),
                    $inquiry->getAttribute('company'),
                    $inquiry->getAttribute('city'),
                    $inquiry->getAttribute('state'),
                    $inquiry->getAttribute('subject'),
                    strip_tags((string) $inquiry->getAttribute('message')),
                    $inquiry->getAttribute('origin'),
                    $inquiry->getAttribute('source_url'),
                    $utm['source'] ?? null,
                    $utm['medium'] ?? null,
                    $utm['campaign'] ?? null,
                    $inquiry->getAttribute('ip'),
                    $inquiry->getAttribute('user_agent'),
                    $status instanceof InquiryStatus ? $status->value : (string) $status,
                    $assignee instanceof User ? $assignee->getAttribute('name') : null,
                    optional($inquiry->getAttribute('first_viewed_at'))->toDateTimeString(),
                    optional($inquiry->getAttribute('first_answered_at'))->toDateTimeString(),
                    optional($inquiry->getAttribute('completed_at'))->toDateTimeString(),
                    optional($inquiry->getAttribute('created_at'))->toDateTimeString(),
                ], ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
