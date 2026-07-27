<?php

declare(strict_types=1);

namespace App\Filament\Resources\CatalogCategoryResource\Pages;

use App\Filament\Resources\CatalogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCatalogCategory extends CreateRecord
{
    protected static string $resource = CatalogCategoryResource::class;
}
