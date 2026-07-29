<?php

declare(strict_types=1);

namespace App\Filament\Resources\CatalogCategoryResource\Pages;

use App\Filament\Resources\CatalogCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogCategories extends ListRecords
{
    protected static string $resource = CatalogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova categoria'),
        ];
    }
}
