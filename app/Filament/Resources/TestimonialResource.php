<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TestimonialStatus;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use App\Services\EngagementService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Depoimentos';

    protected static ?string $modelLabel = 'Depoimento';

    protected static ?string $pluralModelLabel = 'Depoimentos';

    protected static ?int $navigationSort = 60;

    public static function getNavigationGroup(): ?string
    {
        return 'Comunidade';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user')->latest('created_at');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Conteúdo')
                ->schema([
                    Textarea::make('content')
                        ->label('Depoimento')
                        ->rows(6)
                        ->disabled(),
                    TextInput::make('score')
                        ->label('Nota')
                        ->numeric()
                        ->disabled(),
                    TextInput::make('name')
                        ->label('Nome')
                        ->disabled(),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->disabled(),
                    TextInput::make('city')
                        ->label('Cidade')
                        ->disabled(),
                ]),
            Section::make('Moderação')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(collect(TestimonialStatus::cases())->mapWithKeys(fn (TestimonialStatus $status) => [$status->value => $status->label()])->all())
                        ->required(),
                    TextInput::make('rejection_reason')
                        ->label('Motivo da rejeição')
                        ->maxLength(120),
                    DateTimePicker::make('approved_at')
                        ->label('Aprovado em')
                        ->disabled(),
                    DateTimePicker::make('rejected_at')
                        ->label('Rejeitado em')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->toggleable(),
                TextColumn::make('city')->label('Cidade')->toggleable(),
                TextColumn::make('score')
                    ->label('Nota')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning'),
                TextColumn::make('content')->label('Conteúdo')->limit(60)->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TestimonialStatus $state): string => $state->color())
                    ->formatStateUsing(fn (TestimonialStatus $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(TestimonialStatus::cases())->mapWithKeys(fn (TestimonialStatus $status) => [$status->value => $status->label()])->all()),
                SelectFilter::make('score')
                    ->label('Nota')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Testimonial $record): bool => $record->status !== TestimonialStatus::Approved)
                    ->action(function (Testimonial $record): void {
                        app(EngagementService::class)->approve($record);
                    }),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Testimonial $record): bool => $record->status !== TestimonialStatus::Rejected)
                    ->action(function (Testimonial $record): void {
                        app(EngagementService::class)->reject($record, 'manual');
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('approve')
                    ->label('Aprovar selecionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        $service = app(EngagementService::class);
                        $records->each(function ($record) use ($service): void {
                            if ($record instanceof Testimonial) {
                                $service->approve($record);
                            }
                        });
                    }),
                BulkAction::make('reject')
                    ->label('Rejeitar selecionados')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Collection $records): void {
                        $service = app(EngagementService::class);
                        $records->each(function ($record) use ($service): void {
                            if ($record instanceof Testimonial) {
                                $service->reject($record, 'manual');
                            }
                        });
                    }),
                DeleteBulkAction::make(),
            ])
            ->recordUrl(fn (Testimonial $record) => TestimonialResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
