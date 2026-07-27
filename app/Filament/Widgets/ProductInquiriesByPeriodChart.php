<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ProductInquiry;
use Filament\Widgets\ChartWidget;

class ProductInquiriesByPeriodChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Consultas por período (últimos 30 dias)';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $counts = ProductInquiry::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $start->copy()->addDays($i)->format('d/m');
            $values[] = (int) ($counts[$day] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Consultas',
                    'data' => $values,
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
