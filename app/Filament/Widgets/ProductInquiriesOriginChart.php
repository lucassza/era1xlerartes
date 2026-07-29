<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ProductInquiry;
use Filament\Widgets\ChartWidget;

class ProductInquiriesOriginChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function getHeading(): ?string
    {
        return 'Origem das consultas';
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $rows = ProductInquiry::query()
            ->whereNotNull('origin')
            ->selectRaw('origin, COUNT(*) as total')
            ->groupBy('origin')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
                    'backgroundColor' => [
                        '#650010', '#E95D78', '#4A90E2', '#4CAF50',
                        '#F4B400', '#666666', '#F7B8C6', '#2F2F35',
                    ],
                ],
            ],
            'labels' => $rows->pluck('origin')->all(),
        ];
    }
}
