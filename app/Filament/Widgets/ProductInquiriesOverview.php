<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\InquiryStatus;
use App\Services\ProductInquiryService;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductInquiriesOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = app(ProductInquiryService::class)->dashboardStats();

        $primary = Color::hex('#d42020');
        $accent = Color::hex('#e6287e');
        $warning = Color::hex('#b45309');
        $info = Color::hex('#0ea5e9');
        $success = Color::hex('#2fb344');

        $avgResponse = $stats['avg_first_response_minutes'] === null
            ? '—'
            : (string) $stats['avg_first_response_minutes'].' min';

        return [
            Stat::make('Consultas novas', (string) $stats['new'])
                ->description('Ainda não visualizadas')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($stats['new'] > 0 ? $warning : 'gray')
                ->url(route('filament.admin.resources.product-inquiries.index', [
                    'tableFilters[status][values]' => [InquiryStatus::New->value],
                ])),
            Stat::make('Em atendimento', (string) $stats['in_service'])
                ->description('Em acompanhamento pelo admin')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($stats['in_service'] > 0 ? $info : 'gray')
                ->url(route('filament.admin.resources.product-inquiries.index', [
                    'tableFilters[status][values]' => [InquiryStatus::InService->value],
                ])),
            Stat::make('Concluídas', (string) $stats['completed'])
                ->description("Taxa de conclusão: {$stats['completion_rate']}%")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($success),
            Stat::make('Tempo médio de 1º atendimento', $avgResponse)
                ->description('Entre o envio e o início do atendimento')
                ->descriptionIcon('heroicon-m-clock')
                ->color($accent),
        ];
    }
}
