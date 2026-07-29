<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductInquiry;
use App\Models\ProductInquiryNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductInquiryNote>
 */
class ProductInquiryNoteFactory extends Factory
{
    protected $model = ProductInquiryNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_inquiry_id' => ProductInquiry::factory(),
            'user_id' => User::factory()->admin(),
            'note' => fake()->paragraph(),
        ];
    }
}
