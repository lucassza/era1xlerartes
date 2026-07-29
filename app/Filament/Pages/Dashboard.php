<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\EngagementOverviewWidget;
use App\Filament\Widgets\ProductInquiriesByPeriodChart;
use App\Filament\Widgets\ProductInquiriesOriginChart;
use App\Filament\Widgets\ProductInquiriesOverview;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\TopConsultedCategoriesWidget;
use App\Filament\Widgets\TopConsultedProductsWidget;
use App\Filament\Widgets\TopRatedPostsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $sort = 1;

    public function getWidgets(): array
    {
        return [
            ContentStatsOverview::class,
            EngagementOverviewWidget::class,
            ProductInquiriesOverview::class,
            ProductInquiriesByPeriodChart::class,
            TopConsultedProductsWidget::class,
            TopConsultedCategoriesWidget::class,
            ProductInquiriesOriginChart::class,
            TopRatedPostsWidget::class,
            RecentActivityWidget::class,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }
}
