<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductInquiry>
 */
class ProductInquiryFactory extends Factory
{
    protected $model = ProductInquiry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->published()->create();

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->cellphoneNumber(),
            'company' => fake()->optional()->company(),
            'city' => fake()->city(),
            'state' => strtoupper(fake()->randomLetter()).fake()->randomLetter(),
            'subject' => fake()->sentence(6),
            'message' => fake()->paragraph(),
            'origin' => 'web',
            'source_url' => fake()->url(),
            'utm' => ['source' => 'google', 'medium' => 'cpc'],
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'status' => InquiryStatus::New,
            'assignee_id' => null,
        ];
    }

    public function inService(): static
    {
        return $this->state(fn () => [
            'status' => InquiryStatus::InService,
            'first_answered_at' => now()->subHour(),
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn () => [
            'status' => InquiryStatus::Spam,
        ]);
    }

    public function assigned(?User $user = null): static
    {
        return $this->state(fn () => [
            'assignee_id' => $user?->id ?? User::factory()->admin(),
        ]);
    }
}
