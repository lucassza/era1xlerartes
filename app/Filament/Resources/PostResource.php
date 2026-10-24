<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PostStatus;
use App\Filament\Forms\Components\HtmlEditor;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Posts';

    protected static ?string $modelLabel = 'Post';

    protected static ?string $pluralModelLabel = 'Posts';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Conteúdo';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['author.user', 'category', 'tags']);

        if (auth()->user()?->hasRole('Editor')) {
            return $query;
        }

        $author = auth()->user()?->author;

        return $author ? $query->whereBelongsTo($author) : $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('author_id')
                ->label('Autor')
                ->relationship('author', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('category_id')
                ->label('Categoria')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('tags')
                ->label('Tags')
                ->multiple()
                ->relationship('tags', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Textarea::make('excerpt')
                ->label('Resumo')
                ->rows(3),
            HtmlEditor::make('content')
                ->label('Conteúdo')
                ->editorHeight(480)
                ->required(),
            Select::make('status')
                ->label('Status')
                ->options(collect(PostStatus::cases())->mapWithKeys(fn (PostStatus $status) => [$status->value => $status->label()])->all())
                ->required()
                ->default(PostStatus::Draft->value),
            DateTimePicker::make('published_at')
                ->label('Publicação em'),
            DateTimePicker::make('archived_at')
                ->label('Arquivado em'),
            Toggle::make('comments_locked')
                ->label('Bloquear comentários neste post')
                ->helperText('Quando ativo, ninguém pode comentar mesmo com a flag global habilitada.')
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')
                ->label('Título')
                ->searchable()
                ->sortable(),
            TextColumn::make('author.name')
                ->label('Autor')
                ->searchable(),
            TextColumn::make('category.name')
                ->label('Categoria')
                ->searchable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (PostStatus $state): string => $state->color())
                ->formatStateUsing(fn (PostStatus $state): string => $state->label()),
            IconColumn::make('comments_locked')
                ->label('Comentários')
                ->boolean()
                ->trueIcon('heroicon-o-lock-closed')
                ->falseIcon('heroicon-o-chat-bubble-bottom-center-text'),
            TextColumn::make('published_at')
                ->label('Publicação')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
