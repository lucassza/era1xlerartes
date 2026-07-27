<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogCategoryResource\Pages;
use App\Models\CatalogCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CatalogCategoryResource extends Resource
{
    protected static ?string $model = CatalogCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Categorias';

    protected static ?string $modelLabel = 'Categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('sort_order')->orderBy('name');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->tabs([
                Tabs\Tab::make('Geral')->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Gerado automaticamente a partir do nome, se vazio.'),
                    Select::make('parent_id')
                        ->label('Categoria pai')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Sem pai (categoria raiz)')
                        ->helperText('Define a hierarquia. Categorias cíclicas são bloqueadas ao salvar.'),
                    TextInput::make('icon')
                        ->label('Ícone')
                        ->placeholder('heroicon-o-tag')
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Descrição')
                        ->rows(3),
                    TextInput::make('sort_order')
                        ->label('Ordem de exibição')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Toggle::make('is_active')
                        ->label('Ativa')
                        ->default(true)
                        ->inline(false),
                ]),
                Tabs\Tab::make('Imagem')->schema([
                    FileUpload::make('image')
                        ->label('Imagem da categoria')
                        ->image()
                        ->disk(config('media-library.disk_name'))
                        ->directory('catalog/categories-tmp')
                        ->dehydrated(false),
                ]),
                Tabs\Tab::make('SEO')->schema([
                    TextInput::make('seo_title')
                        ->label('Título SEO')
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label('Descrição SEO')
                        ->rows(3)
                        ->maxLength(500),
                ]),
            ])->columnSpanFull(),
            Hidden::make('guard_against_cycle')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->disk(config('media-library.disk_name'))
                    ->circular()
                    ->defaultImageUrl(fn () => null),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, CatalogCategory $record): string {
                        $parentName = $record->getAttribute('parent')?->getAttribute('name');

                        return $parentName
                            ? "— {$parentName} > {$record->getAttribute('name')}"
                            : (string) $record->getAttribute('name');
                    }),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ])
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatalogCategories::route('/'),
            'create' => Pages\CreateCatalogCategory::route('/create'),
            'edit' => Pages\EditCatalogCategory::route('/{record}/edit'),
        ];
    }
}
