<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\CatalogEditorialService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(CatalogEditorialService::class)->normalizeData($data);
    }

    protected function afterSave(): void
    {
        if (! $this->record instanceof Product) {
            return;
        }

        app(CatalogEditorialService::class)->syncMedia($this->record, $this->data);
        app(CatalogEditorialService::class)->syncAttributes($this->record, $this->data);
    }
}
