<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductInquiryResource\Pages;

use App\Enums\InquiryStatus;
use App\Filament\Resources\ProductInquiryResource;
use App\Models\ProductInquiry;
use App\Models\User;
use App\Services\ProductInquiryService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditProductInquiry extends EditRecord
{
    protected static string $resource = ProductInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Voltar para visualização')
                ->url(fn (): string => ProductInquiryResource::getUrl('view', ['record' => $this->record])),
            DeleteAction::make()->label('Excluir consulta'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label('Status')
                ->options(collect(InquiryStatus::cases())
                    ->mapWithKeys(fn (InquiryStatus $s) => [$s->value => $s->label()])
                    ->all())
                ->required()
                ->live(),
            Select::make('assignee_id')
                ->label('Responsável')
                ->options(User::query()
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Editor']))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->placeholder('Sem responsável'),
            Textarea::make('message')
                ->label('Mensagem')
                ->rows(6)
                ->disabled()
                ->dehydrated(false),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! $this->record instanceof ProductInquiry) {
            return $data;
        }

        $service = app(ProductInquiryService::class);
        $newStatus = InquiryStatus::from($data['status']);

        $currentStatus = $this->record->getAttribute('status');
        $previous = $currentStatus instanceof InquiryStatus
            ? $currentStatus
            : InquiryStatus::from((string) $currentStatus);

        if ($previous !== $newStatus) {
            $service->changeStatus($this->record, $newStatus, auth()->user(), 'Alterado pelo painel');
        }

        if (array_key_exists('assignee_id', $data)) {
            $assignee = $data['assignee_id']
                ? User::query()->find($data['assignee_id'])
                : null;
            $service->assign($this->record, $assignee, auth()->user());
        }

        return [
            'status' => $data['status'] ?? null,
        ];
    }
}
