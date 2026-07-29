<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\CatalogCategoryResource;
use App\Models\ProductInquiry;
use Filament\Widgets\Widget;

class TopConsultedCategoriesWidget extends Widget
{
    protected string $view = 'filament.widgets.top-consulted-list';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function getHeading(): ?string
    {
        return 'Categorias mais consultadas';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $rows = ProductInquiry::query()
            ->join('products', 'products.id', '=', 'product_inquiries.product_id')
            ->join('catalog_categories', 'catalog_categories.id', '=', 'products.catalog_category_id')
            ->whereNotNull('products.catalog_category_id')
            ->groupBy('catalog_categories.id', 'catalog_categories.name')
            ->selectRaw('catalog_categories.id, catalog_categories.name, COUNT(*) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($row): array {
                $url = CatalogCategoryResource::getUrl('edit', ['record' => $row->id]);

                return [
                    'name' => (string) ($row->name ?? ''),
                    'total' => (int) ($row->total ?? 0),
                    'url' => $url,
                ];
            })
            ->values()
            ->all();

        return [
            'rows' => $rows,
        ];
    }
}
