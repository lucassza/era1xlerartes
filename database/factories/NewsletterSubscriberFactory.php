<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NewsletterSubscriberStatus;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'status' => NewsletterSubscriberStatus::Pending,
            'token' => Str::random(40),
            'confirmed_at' => null,
            'unsubscribed_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'status' => NewsletterSubscriberStatus::Confirmed,
            'confirmed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }
}
