<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\CatalogEditorialService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(CatalogEditorialService::class)->normalizeData($data);
    }

    protected function afterCreate(): void
    {
        if (! $this->record instanceof Product) {
            return;
        }

        app(CatalogEditorialService::class)->syncMedia($this->record, $this->data);
        app(CatalogEditorialService::class)->syncAttributes($this->record, $this->data);
    }
}
