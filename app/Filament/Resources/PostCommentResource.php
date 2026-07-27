<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PostCommentStatus;
use App\Filament\Resources\PostCommentResource\Pages;
use App\Models\Post;
use App\Models\PostComment;
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

class PostCommentResource extends Resource
{
    protected static ?string $model = PostComment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationLabel = 'Comentários';

    protected static ?string $modelLabel = 'Comentário';

    protected static ?string $pluralModelLabel = 'Comentários';

    protected static ?int $navigationSort = 70;

    public static function getNavigationGroup(): ?string
    {
        return 'Comunidade';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['post', 'user'])->latest('created_at');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Conteúdo')
                ->schema([
                    Select::make('post_id')
                        ->label('Post')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(),
                    Textarea::make('content')
                        ->label('Comentário')
                        ->rows(5)
                        ->disabled(),
                    TextInput::make('score')
                        ->label('Nota')
                        ->numeric()
                        ->disabled(),
                    TextInput::make('name')->label('Nome')->disabled(),
                    TextInput::make('email')->label('E-mail')->disabled(),
                ]),
            Section::make('Moderação')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(collect(PostCommentStatus::cases())->mapWithKeys(fn (PostCommentStatus $status) => [$status->value => $status->label()])->all())
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
                TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->url(function (PostComment $record): ?string {
                        $post = $record->post;

                        return $post instanceof Post ? route('posts.show', $post->slug) : null;
                    }),
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->toggleable(),
                TextColumn::make('score')
                    ->label('Nota')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning'),
                TextColumn::make('content')->label('Comentário')->limit(60)->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PostCommentStatus $state): string => $state->color())
                    ->formatStateUsing(fn (PostCommentStatus $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->options(Post::query()->orderBy('title')->pluck('title', 'id')->all())
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(PostCommentStatus::cases())->mapWithKeys(fn (PostCommentStatus $status) => [$status->value => $status->label()])->all()),
                SelectFilter::make('score')
                    ->label('Nota')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PostComment $record): bool => $record->status !== PostCommentStatus::Approved)
                    ->action(function (PostComment $record): void {
                        app(EngagementService::class)->approve($record);
                    }),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PostComment $record): bool => $record->status !== PostCommentStatus::Rejected)
                    ->action(function (PostComment $record): void {
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
                            if ($record instanceof PostComment) {
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
                            if ($record instanceof PostComment) {
                                $service->reject($record, 'manual');
                            }
                        });
                    }),
                DeleteBulkAction::make(),
            ])
            ->recordUrl(fn (PostComment $record) => PostCommentResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostComments::route('/'),
            'edit' => Pages\EditPostComment::route('/{record}/edit'),
        ];
    }
}
