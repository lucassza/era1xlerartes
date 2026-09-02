<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Papéis';

    protected static ?string $modelLabel = 'Papel';

    protected static ?string $pluralModelLabel = 'Papéis';

    protected static ?int $navigationSort = 80;

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nome')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('guard_name')->label('Guard')->required()->default('web')->maxLength(255),
            Select::make('permissions')
                ->label('Permissões')
                ->multiple()
                ->relationship('permissions', 'name')
                ->options(Permission::query()->orderBy('name')->pluck('name', 'id')->all())
                ->preload()
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('guard_name')->label('Guard')->badge(),
            TextColumn::make('permissions_count')->counts('permissions')->label('Permissões'),
        ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
