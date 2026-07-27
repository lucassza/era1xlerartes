<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductInquiryResource\Pages;

use App\Filament\Exports\ProductInquiryCsvExporter;
use App\Filament\Resources\ProductInquiryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListProductInquiries extends ListRecords
{
    protected static string $resource = ProductInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exportar CSV (filtros aplicados)')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): StreamedResponse {
                    $records = static::$resource::getEloquentQuery()->get();

                    return ProductInquiryCsvExporter::stream($records->all());
                }),
        ];
    }
}
