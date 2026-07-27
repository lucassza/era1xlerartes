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
                        '#d42020', '#e6287e', '#0ea5e9', '#2fb344',
                        '#b45309', '#7a5555', '#00bcd8', '#475569',
                    ],
                ],
            ],
            'labels' => $rows->pluck('origin')->all(),
        ];
    }
}
