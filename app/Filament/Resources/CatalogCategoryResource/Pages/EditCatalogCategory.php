<?php

declare(strict_types=1);

namespace App\Filament\Resources\CatalogCategoryResource\Pages;

use App\Filament\Resources\CatalogCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogCategory extends EditRecord
{
    protected static string $resource = CatalogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
