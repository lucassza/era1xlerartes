<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'city' => fake()->optional(0.6)->city(),
            'content' => fake()->paragraph(),
            'score' => fake()->numberBetween(3, 5),
            'status' => TestimonialStatus::Pending,
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'ip' => fake()->ipv4(),
            'user_agent' => Str::limit((string) fake()->userAgent(), 250, ''),
        ];
    }

    public function configure(): static
    {
        return $this;
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => TestimonialStatus::Approved,
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => TestimonialStatus::Pending,
            'approved_at' => null,
            'rejected_at' => null,
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn () => [
            'status' => TestimonialStatus::Rejected,
            'approved_at' => null,
            'rejected_at' => now(),
            'rejection_reason' => $reason ?? 'manual',
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'city' => $user->city,
        ]);
    }
}
