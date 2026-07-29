<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductInquiry;
use Filament\Widgets\Widget;

class TopConsultedProductsWidget extends Widget
{
    protected string $view = 'filament.widgets.top-consulted-list';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function getHeading(): ?string
    {
        return 'Produtos mais consultados';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $rows = ProductInquiry::query()
            ->selectRaw('product_id, COUNT(*) as total')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(function (ProductInquiry $inquiry): array {
                $product = $inquiry->product;
                $name = $product instanceof Product
                    ? (string) $product->getAttribute('name')
                    : (string) $inquiry->getAttribute('product_name');

                return [
                    'name' => $name,
                    'total' => (int) $inquiry->getAttribute('total'),
                    'url' => $product instanceof Product
                        ? ProductResource::getUrl('edit', ['record' => $product])
                        : null,
                ];
            })
            ->values()
            ->all();

        return [
            'rows' => $rows,
        ];
    }
}
