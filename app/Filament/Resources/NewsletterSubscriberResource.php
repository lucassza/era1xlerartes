<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NewsletterSubscriberStatus;
use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $modelLabel = 'Inscrito';

    protected static ?string $pluralModelLabel = 'Inscritos';

    protected static ?int $navigationSort = 100;

    public static function getNavigationGroup(): ?string
    {
        return 'Comunidade';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->label('E-mail')->email()->required()->disabled(),
            Select::make('status')
                ->label('Status')
                ->options(collect(NewsletterSubscriberStatus::cases())->mapWithKeys(fn (NewsletterSubscriberStatus $status) => [$status->value => $status->label()])->all())
                ->required(),
            DateTimePicker::make('confirmed_at')->label('Confirmado em'),
            DateTimePicker::make('unsubscribed_at')->label('Cancelado em'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('email')->label('E-mail')->searchable()->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (NewsletterSubscriberStatus $state): string => $state->color())
                ->formatStateUsing(fn (NewsletterSubscriberStatus $state): string => $state->label()),
            TextColumn::make('confirmed_at')->label('Confirmado em')->dateTime('d/m/Y H:i')->sortable(),
            TextColumn::make('unsubscribed_at')->label('Cancelado em')->dateTime('d/m/Y H:i')->sortable(),
            TextColumn::make('updated_at')->label('Atualizado em')->dateTime('d/m/Y H:i')->sortable(),
        ])
            ->actions([EditAction::make()->label('Editar')])
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
