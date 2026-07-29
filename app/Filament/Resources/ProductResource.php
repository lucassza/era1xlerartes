<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PriceDisplayMode;
use App\Enums\ProductAvailability;
use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Attribute;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'brand', 'media']);
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
                        ->helperText('Gerado automaticamente a partir do nome.'),
                    TextInput::make('internal_code')
                        ->label('Código interno')
                        ->maxLength(255),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(255),
                    Select::make('catalog_category_id')
                        ->label('Categoria')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Sem categoria'),
                    Select::make('brand_id')
                        ->label('Marca')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Sem marca'),
                    Textarea::make('short_description')
                        ->label('Descrição curta')
                        ->rows(2)
                        ->maxLength(500)
                        ->helperText('Aparece nos cards do catálogo.'),
                    Textarea::make('description')
                        ->label('Descrição completa')
                        ->rows(8),
                    Textarea::make('specifications')
                        ->label('Especificações')
                        ->rows(4),
                    Textarea::make('features')
                        ->label('Características')
                        ->rows(4),
                    Toggle::make('is_featured')
                        ->label('Produto em destaque')
                        ->inline(false),
                    TextInput::make('sort_order')
                        ->label('Ordem manual')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Quando definido, usado em ordenação por "ordem manual".'),
                ]),
                Tabs\Tab::make('Preço e disponibilidade')->schema([
                    Section::make('Preço')->schema([
                        TextInput::make('price')
                            ->label('Preço')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01),
                        TextInput::make('promotional_price')
                            ->label('Preço promocional')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->helperText('Apenas informativo; não há fluxo de venda.'),
                        Select::make('price_display_mode')
                            ->label('Exibição do preço')
                            ->options(collect(PriceDisplayMode::cases())
                                ->mapWithKeys(fn (PriceDisplayMode $m) => [$m->value => $m->label()])
                                ->all())
                            ->default(PriceDisplayMode::Show->value)
                            ->required()
                            ->live(),
                        TextInput::make('currency')
                            ->label('Moeda')
                            ->default('BRL')
                            ->maxLength(3),
                        TextInput::make('unit')
                            ->label('Unidade')
                            ->placeholder('un, kg, m…')
                            ->maxLength(20),
                    ])->columns(2),
                    Section::make('Disponibilidade')->schema([
                        Select::make('availability')
                            ->label('Disponibilidade')
                            ->options(collect(ProductAvailability::cases())
                                ->mapWithKeys(fn (ProductAvailability $a) => [$a->value => $a->label()])
                                ->all())
                            ->default(ProductAvailability::Available->value)
                            ->required(),
                        TextInput::make('informational_stock')
                            ->label('Estoque informativo')
                            ->numeric()
                            ->helperText('Apenas exibição; sem controle de venda.'),
                        TextInput::make('inquiry_button_text')
                            ->label('Texto do botão "Consulte"')
                            ->placeholder('Ex.: Solicite um orçamento')
                            ->maxLength(100)
                            ->helperText('Se vazio, usa o padrão configurado em Configurações do Catálogo.'),
                    ])->columns(2),
                ]),
                Tabs\Tab::make('Mídia')->schema([
                    FileUpload::make('cover')
                        ->label('Imagem principal')
                        ->image()
                        ->disk(config('media-library.disk_name'))
                        ->directory('catalog/products-tmp')
                        ->dehydrated(false)
                        ->maxFiles(1)
                        ->imageEditor()
                        ->helperText('Aceita JPG, PNG e WebP até 32 MB. Será otimizada e convertida.'),
                    FileUpload::make('gallery')
                        ->label('Galeria de imagens')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->disk(config('media-library.disk_name'))
                        ->directory('catalog/products-tmp')
                        ->dehydrated(false)
                        ->imageEditor(),
                    FileUpload::make('attachments')
                        ->label('Arquivos anexos (PDF, manuais, fichas técnicas)')
                        ->multiple()
                        ->disk(config('media-library.disk_name'))
                        ->directory('catalog/products-tmp')
                        ->dehydrated(false)
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->helperText('Tamanho máximo por arquivo: 32 MB. Extensões perigosas são bloqueadas.'),
                ]),
                Tabs\Tab::make('Tags e atributos')->schema([
                    Select::make('tags')
                        ->label('Tags (compartilhadas com o blog)')
                        ->multiple()
                        ->relationship('tags', 'name')
                        ->searchable()
                        ->preload(),
                    Repeater::make('attribute_values')
                        ->label('Atributos informativos')
                        ->schema([
                            Select::make('attribute_id')
                                ->label('Atributo')
                                ->options(fn () => Attribute::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                            TextInput::make('value')
                                ->label('Valor')
                                ->maxLength(500),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state): ?string => Attribute::find($state['attribute_id'] ?? null)?->name)
                        ->collapsible()
                        ->collapsed()
                        ->helperText('Atributos são apenas informativos.'),
                ]),
                Tabs\Tab::make('Relacionados')->schema([
                    Select::make('relatedProducts')
                        ->label('Produtos relacionados')
                        ->multiple()
                        ->relationship(
                            name: 'relatedProducts',
                            titleAttribute: 'name',
                        )
                        ->searchable()
                        ->preload()
                        ->helperText('Selecione até 12 produtos publicados para exibir no detalhe do produto.'),
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
                Tabs\Tab::make('Publicação')->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(collect(ProductStatus::cases())
                            ->mapWithKeys(fn (ProductStatus $s) => [$s->value => $s->label()])
                            ->all())
                        ->default(ProductStatus::Draft->value)
                        ->required(),
                    DateTimePicker::make('published_at')
                        ->label('Data de publicação')
                        ->helperText('Definida automaticamente quando o status muda para "Publicado".'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->label('Imagem')
                    ->disk(config('media-library.disk_name'))
                    ->imageHeight(48)
                    ->width(48)
                    ->extraImgAttributes(['style' => 'object-fit: cover;'])
                    ->defaultImageUrl(null),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->label('Marca')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('price_display_mode')
                    ->label('Preço')
                    ->badge()
                    ->formatStateUsing(fn (PriceDisplayMode $state): string => match ($state) {
                        PriceDisplayMode::Show => 'Exibe',
                        PriceDisplayMode::OnRequest => 'Sob consulta',
                        PriceDisplayMode::Hidden => 'Oculto',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ProductStatus $state): string => $state->color())
                    ->formatStateUsing(fn (ProductStatus $state): string => $state->label()),
                IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ProductStatus::cases())
                        ->mapWithKeys(fn (ProductStatus $s) => [$s->value => $s->label()])
                        ->all()),
                SelectFilter::make('catalog_category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_featured')
                    ->label('Em destaque'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
