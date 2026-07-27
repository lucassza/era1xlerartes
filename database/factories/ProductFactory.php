<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PriceDisplayMode;
use App\Enums\ProductAvailability;
use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::headline(fake()->unique()->words(3, true));

        return [
            'catalog_category_id' => CatalogCategory::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'internal_code' => (string) fake()->unique()->numberBetween(1000, 9999),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'short_description' => fake()->sentence(),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'specifications' => fake()->paragraph(),
            'features' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 5000),
            'promotional_price' => null,
            'price_display_mode' => PriceDisplayMode::Show,
            'currency' => 'BRL',
            'unit' => 'un',
            'availability' => ProductAvailability::Available,
            'informational_stock' => fake()->numberBetween(1, 50),
            'inquiry_button_text' => null,
            'is_featured' => false,
            'sort_order' => 0,
            'status' => ProductStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ProductStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
            'status' => ProductStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn () => [
            'status' => ProductStatus::Unavailable,
            'published_at' => now()->subDays(5),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => ProductStatus::Archived,
            'published_at' => now()->subMonths(2),
        ]);
    }

    public function onRequest(): static
    {
        return $this->state(fn () => [
            'price_display_mode' => PriceDisplayMode::OnRequest,
            'price' => null,
        ]);
    }
}
