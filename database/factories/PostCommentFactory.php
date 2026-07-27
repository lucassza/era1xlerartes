<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostCommentStatus;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostComment>
 */
class PostCommentFactory extends Factory
{
    protected $model = PostComment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->published(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'content' => fake()->paragraph(),
            'score' => fake()->numberBetween(3, 5),
            'status' => PostCommentStatus::Pending,
            'approved_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'ip' => fake()->ipv4(),
            'user_agent' => Str::limit((string) fake()->userAgent(), 250, ''),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => PostCommentStatus::Approved,
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => PostCommentStatus::Pending,
            'approved_at' => null,
            'rejected_at' => null,
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn () => [
            'status' => PostCommentStatus::Rejected,
            'approved_at' => null,
            'rejected_at' => now(),
            'rejection_reason' => $reason ?? 'manual',
        ]);
    }

    public function forPost(Post $post): static
    {
        return $this->state(fn () => [
            'post_id' => $post->getKey(),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
