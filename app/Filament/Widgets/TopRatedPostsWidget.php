<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopRatedPostsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top 5 posts por avaliação')
            ->description('Média calculada a partir dos comentários aprovados.')
            ->query(
                Post::query()
                    ->published()
                    ->whereHas('approvedComments')
                    ->withAvg('approvedComments as average_rating', 'score')
                    ->withCount('approvedComments as ratings_count')
                    ->orderByDesc('average_rating')
                    ->orderByDesc('ratings_count')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Post')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('average_rating')
                    ->label('Nota média')
                    ->numeric(1, ',', '.')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('ratings_count')
                    ->label('Avaliações')
                    ->badge()
                    ->color('info'),
            ])
            ->paginated(false);
    }
}
