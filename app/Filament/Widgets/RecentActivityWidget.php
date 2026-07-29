<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Atividade recente')
            ->description('Últimos 10 registros do log de auditoria.')
            ->query(
                Activity::query()->latest('created_at')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Evento')
                    ->wrap(),
                Tables\Columns\TextColumn::make('causer_type')
                    ->label('Autor')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Alvo')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
            ])
            ->paginated(false);
    }
}
