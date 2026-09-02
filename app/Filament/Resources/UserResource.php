<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserType;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return 'Comunidade';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identidade')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('city')
                        ->label('Cidade')
                        ->maxLength(120),
                ]),
            Section::make('Acesso')
                ->schema([
                    Select::make('type')
                        ->label('Tipo de usuário')
                        ->options(collect(UserType::cases())->mapWithKeys(fn (UserType $type) => [$type->value => $type->label()])->all())
                        ->required()
                        ->default(UserType::SiteUser->value)
                        ->helperText('Admins acessam o painel. Usuários do site apenas interagem.'),
                    TextInput::make('password')
                        ->label('Senha')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create'),
                    Select::make('roles')
                        ->label('Papéis')
                        ->multiple()
                        ->options(Role::query()->orderBy('name')->pluck('name', 'name')->all())
                        ->dehydrated(false)
                        ->preload()
                        ->searchable(),
                ]),
            Section::make('Status')
                ->schema([
                    Toggle::make('email_verified')
                        ->label('E-mail verificado')
                        ->dehydrated(false)
                        ->afterStateHydrated(fn (Toggle $component, ?User $record) => $component->state($record?->hasVerifiedEmail() ?? false))
                        ->disabled(fn (?User $record): bool => $record === null),
                    DateTimePicker::make('email_verified_at')
                        ->label('Verificado em')
                        ->disabled()
                        ->dehydrated(false),
                    DateTimePicker::make('banned_at')
                        ->label('Bloqueado em')
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (UserType $state): string => $state->label())
                    ->color(fn (UserType $state): string => $state === UserType::Admin ? 'primary' : 'gray')
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Cidade')
                    ->toggleable(),
                IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-envelope')
                    ->sortable(),
                IconColumn::make('banned_at')
                    ->label('Bloqueado')
                    ->boolean()
                    ->trueIcon('heroicon-o-no-symbol')
                    ->falseIcon('heroicon-o-check')
                    ->sortable(),
                TextColumn::make('testimonials_count')
                    ->label('Depoimentos')
                    ->counts('testimonials')
                    ->toggleable(),
                TextColumn::make('comments_count')
                    ->label('Comentários')
                    ->counts('comments')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(collect(UserType::cases())->mapWithKeys(fn (UserType $type) => [$type->value => $type->label()])->all()),
                TernaryFilter::make('banned_at')
                    ->label('Bloqueado')
                    ->nullable(),
                TernaryFilter::make('email_verified_at')
                    ->label('E-mail verificado')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
