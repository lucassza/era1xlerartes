<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Posts', (string) Post::query()->count()),
            Stat::make('Publicados', (string) Post::query()->where('status', PostStatus::Published)->count()),
            Stat::make('Agendados', (string) Post::query()->where('status', PostStatus::Scheduled)->count()),
            Stat::make('Autores ativos', (string) Author::query()->where('is_active', true)->count()),
            Stat::make('Categorias', (string) Category::query()->count()),
            Stat::make('Tags', (string) Tag::query()->count()),
        ];
    }
}
