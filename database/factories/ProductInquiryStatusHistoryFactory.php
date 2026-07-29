<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductInquiryStatusHistory>
 */
class ProductInquiryStatusHistoryFactory extends Factory
{
    protected $model = ProductInquiryStatusHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_inquiry_id' => ProductInquiry::factory(),
            'from_status' => InquiryStatus::New,
            'to_status' => InquiryStatus::InService,
            'user_id' => User::factory()->admin(),
            'note' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }
}
