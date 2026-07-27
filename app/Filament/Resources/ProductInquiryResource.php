<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\InquiryStatus;
use App\Filament\Exports\ProductInquiryCsvExporter;
use App\Filament\Resources\ProductInquiryResource\Pages;
use App\Models\CatalogCategory;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use App\Services\ProductInquiryService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductInquiryResource extends Resource
{
    protected static ?string $model = ProductInquiry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Consultas de produtos';

    protected static ?string $modelLabel = 'Consulta';

    protected static ?string $pluralModelLabel = 'Consultas';

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['product', 'assignee']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_unread')
                    ->label('')
                    ->getStateUsing(fn (ProductInquiry $record): bool => $record->first_viewed_at === null)
                    ->boolean()
                    ->trueIcon('heroicon-s-envelope')
                    ->falseIcon('heroicon-s-envelope-open')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('product_name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->url(function (ProductInquiry $record): ?string {
                        $product = $record->product;

                        return $product instanceof Product
                            ? ProductResource::getUrl('edit', ['record' => $product])
                            : null;
                    })
                    ->placeholder(fn (ProductInquiry $record): string => $record->product_name),
                TextColumn::make('name')
                    ->label('Interessado')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefone')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (InquiryStatus $state): string => $state->color())
                    ->formatStateUsing(fn (InquiryStatus $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Responsável')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('origin')
                    ->label('Origem')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Recebida em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(InquiryStatus::cases())
                        ->mapWithKeys(fn (InquiryStatus $s) => [$s->value => $s->label()])
                        ->all())
                    ->multiple(),
                SelectFilter::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('catalog_category')
                    ->label('Categoria')
                    ->options(fn (): array => CatalogCategory::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $category = CatalogCategory::query()->find($data['value']);

                        return $query->whereHas('product', fn (Builder $q) => $q->whereIn(
                            'catalog_category_id',
                            $category ? [$category->id, ...$category->descendantIds()->all()] : [$data['value']],
                        ));
                    }),
                SelectFilter::make('assignee_id')
                    ->label('Responsável')
                    ->options(fn (): array => User::query()
                        ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Editor']))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                SelectFilter::make('origin')
                    ->label('Origem')
                    ->options(fn (): array => ProductInquiry::query()
                        ->whereNotNull('origin')
                        ->distinct()
                        ->orderBy('origin')
                        ->pluck('origin')
                        ->mapWithKeys(fn (string $o) => [$o => $o])
                        ->all()),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('date_from')->label('De'),
                        DatePicker::make('date_to')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'] ?? null, fn (Builder $q, string $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['date_to'] ?? null, fn (Builder $q, string $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductInquiry $record): string => ProductInquiryResource::getUrl('view', ['record' => $record])),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (ProductInquiry $record): bool => filled($record->phone))
                    ->url(function (ProductInquiry $record): string {
                        $phone = preg_replace('/\D+/', '', (string) $record->phone) ?? '';

                        return "https://wa.me/{$phone}";
                    })
                    ->openUrlInNewTab(),
                Action::make('copyEmail')
                    ->label('Copiar e-mail')
                    ->icon('heroicon-o-clipboard-document')
                    ->action(function (ProductInquiry $record): void {
                        Notification::make()
                            ->title("E-mail '{$record->email}' enviado para a área de transferência.")
                            ->success()
                            ->send();
                    })
                    ->extraAttributes(fn (ProductInquiry $record): array => [
                        'x-on:click' => "navigator.clipboard.writeText('{$record->email}')",
                    ]),
                Action::make('markSpam')
                    ->label('Marcar como spam')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ProductInquiry $record): bool => $record->status !== InquiryStatus::Spam)
                    ->action(function (ProductInquiry $record): void {
                        app(ProductInquiryService::class)
                            ->changeStatus($record, InquiryStatus::Spam, auth()->user(), 'Marcado como spam');
                    }),
                Action::make('complete')
                    ->label('Concluir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductInquiry $record): bool => $record->status !== InquiryStatus::Completed)
                    ->requiresConfirmation()
                    ->action(function (ProductInquiry $record): void {
                        app(ProductInquiryService::class)
                            ->changeStatus($record, InquiryStatus::Completed, auth()->user(), 'Concluída');
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('markAsInService')
                    ->label('Marcar em atendimento')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Collection $records): void {
                        $service = app(ProductInquiryService::class);
                        $records->each(function ($record) use ($service): void {
                            if ($record instanceof ProductInquiry) {
                                $service->changeStatus($record, InquiryStatus::InService, auth()->user(), 'Atendimento iniciado em massa');
                            }
                        });
                    }),
                BulkAction::make('markAsCompleted')
                    ->label('Marcar como concluídas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $service = app(ProductInquiryService::class);
                        $records->each(function ($record) use ($service): void {
                            if ($record instanceof ProductInquiry) {
                                $service->changeStatus($record, InquiryStatus::Completed, auth()->user(), 'Concluída em massa');
                            }
                        });
                    }),
                BulkAction::make('exportCsv')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records): StreamedResponse {
                        $records->load(['product', 'assignee']);
                        $filtered = $records->filter(fn ($r): bool => $r instanceof ProductInquiry)->values();

                        return ProductInquiryCsvExporter::stream($filtered->all());
                    }),
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ProductInquiry $record) => ProductInquiryResource::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductInquiries::route('/'),
            'view' => Pages\ViewProductInquiry::route('/{record}'),
            'edit' => Pages\EditProductInquiry::route('/{record}/edit'),
        ];
    }
}
