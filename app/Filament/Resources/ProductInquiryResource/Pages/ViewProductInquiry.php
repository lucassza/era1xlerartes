<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductInquiryResource\Pages;

use App\Enums\InquiryStatus;
use App\Filament\Resources\ProductInquiryResource;
use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use App\Services\ProductInquiryService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewProductInquiry extends ViewRecord
{
    protected static string $resource = ProductInquiryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record instanceof ProductInquiry) {
            app(ProductInquiryService::class)->markAsViewed($this->record, auth()->user());
        }
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Produto consultado')
                ->schema([
                    TextEntry::make('product_name')
                        ->label('Produto (no momento da consulta)')
                        ->placeholder('—'),
                    TextEntry::make('product_slug')
                        ->label('Produto atual')
                        ->placeholder('Produto removido')
                        ->formatStateUsing(function ($state, ProductInquiry $record): string {
                            $product = $record->product;

                            return $product instanceof Product
                                ? (string) $product->getAttribute('name')
                                : 'Produto removido';
                        })
                        ->url(function (ProductInquiry $record): ?string {
                            $product = $record->product;

                            return $product instanceof Product
                                ? ProductResource::getUrl('edit', ['record' => $product])
                                : null;
                        }),
                    TextEntry::make('product_url')
                        ->label('URL no site')
                        ->placeholder('—')
                        ->url(fn (ProductInquiry $record): ?string => $record->product instanceof Product
                            ? route('products.show', $record->product->getAttribute('slug'))
                            : null)
                        ->openUrlInNewTab(),
                ])->columns(1),
            Section::make('Interessado')
                ->schema([
                    TextEntry::make('name')->label('Nome'),
                    TextEntry::make('email')->label('E-mail')->copyable(),
                    TextEntry::make('phone')->label('Telefone/WhatsApp')
                        ->copyable()
                        ->suffixAction(
                            Action::make('openWhatsapp')
                                ->icon('heroicon-o-chat-bubble-left-right')
                                ->color('success')
                                ->url(function (ProductInquiry $record): ?string {
                                    $phone = preg_replace('/\D+/', '', (string) $record->getAttribute('phone'));

                                    return $phone ? "https://wa.me/{$phone}" : null;
                                })
                                ->openUrlInNewTab()
                        ),
                    TextEntry::make('company')->label('Empresa')->placeholder('—'),
                    TextEntry::make('city')->label('Cidade')->placeholder('—'),
                    TextEntry::make('state')->label('UF')->placeholder('—'),
                ])->columns(2),
            Section::make('Mensagem')
                ->schema([
                    TextEntry::make('subject')->label('Assunto')->placeholder('—'),
                    TextEntry::make('message')
                        ->label('Mensagem')
                        ->html()
                        ->columnSpanFull(),
                ]),
            Section::make('Origem')
                ->schema([
                    TextEntry::make('origin')->label('Origem')->placeholder('—'),
                    TextEntry::make('source_url')->label('URL de origem')
                        ->placeholder('—')
                        ->url(fn (ProductInquiry $record): ?string => $record->getAttribute('source_url'))
                        ->openUrlInNewTab(),
                    TextEntry::make('ip')->label('IP')->placeholder('—'),
                    TextEntry::make('user_agent')->label('User agent')
                        ->placeholder('—')
                        ->limit(80),
                    TextEntry::make('utm')
                        ->label('UTM')
                        ->placeholder('—')
                        ->formatStateUsing(function ($state): string {
                            if (! is_array($state)) {
                                return '—';
                            }
                            $parts = [];
                            foreach ($state as $k => $v) {
                                $parts[] = "{$k}={$v}";
                            }

                            return implode(' | ', $parts);
                        }),
                ])->columns(2),
            Section::make('Gestão')
                ->schema([
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (InquiryStatus $state): string => $state->color())
                        ->formatStateUsing(fn (InquiryStatus $state): string => $state->label()),
                    TextEntry::make('assignee.name')->label('Responsável')->placeholder('—'),
                    IconEntry::make('first_viewed_at')->label('Visualizada')
                        ->boolean(fn ($state) => filled($state))
                        ->placeholder('—'),
                    TextEntry::make('first_viewed_at')->label('Primeira visualização')->dateTime('d/m/Y H:i'),
                    TextEntry::make('first_answered_at')->label('Primeiro atendimento')->dateTime('d/m/Y H:i'),
                    TextEntry::make('completed_at')->label('Concluída em')->dateTime('d/m/Y H:i'),
                    TextEntry::make('created_at')->label('Recebida em')->dateTime('d/m/Y H:i'),
                ])->columns(2),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeStatus')
                ->label('Alterar status')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Select::make('status')
                        ->label('Novo status')
                        ->options(collect(InquiryStatus::cases())
                            ->mapWithKeys(fn (InquiryStatus $s) => [$s->value => $s->label()])
                            ->all())
                        ->required(),
                    Textarea::make('note')->label('Observação interna (opcional)'),
                ])
                ->action(function (array $data): void {
                    if (! $this->record instanceof ProductInquiry) {
                        return;
                    }
                    $service = app(ProductInquiryService::class);
                    $status = InquiryStatus::from($data['status']);
                    $service->changeStatus($this->record, $status, auth()->user(), $data['note'] ?? null);
                    Notification::make()->title('Status atualizado.')->success()->send();
                }),
            Action::make('assign')
                ->label('Atribuir responsável')
                ->icon('heroicon-o-user-plus')
                ->visible(fn () => auth()->user()?->can('catalog.inquiries.assign'))
                ->schema([
                    Select::make('assignee_id')
                        ->label('Responsável')
                        ->options(User::query()
                            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Editor']))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->placeholder('Sem responsável'),
                ])
                ->action(function (array $data): void {
                    if (! $this->record instanceof ProductInquiry) {
                        return;
                    }
                    $assignee = ! empty($data['assignee_id'])
                        ? User::query()->find($data['assignee_id'])
                        : null;
                    app(ProductInquiryService::class)->assign($this->record, $assignee, auth()->user());
                    Notification::make()->title('Responsável atualizado.')->success()->send();
                }),
            Action::make('addNote')
                ->label('Adicionar observação interna')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    Textarea::make('note')
                        ->label('Observação interna')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    if (! $this->record instanceof ProductInquiry || ! auth()->user()) {
                        return;
                    }
                    app(ProductInquiryService::class)->addNote($this->record, auth()->user(), $data['note']);
                    Notification::make()->title('Observação registrada.')->success()->send();
                }),
            DeleteAction::make()->label('Excluir consulta'),
        ];
    }
}
