<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostCommentStatus;
use App\Enums\TestimonialStatus;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class EngagementService
{
    public function __construct(
        private readonly ProfanityFilter $profanityFilter,
        private readonly Request $request,
    ) {}

    public function submitTestimonial(User $user, array $data): Testimonial
    {
        $this->guardAgainstBan($user);
        $this->guardAgainstSpam($user, 'testimonial');

        $content = (string) ($data['content'] ?? '');
        $sanitizedContent = app(ContentSanitizer::class)->sanitize($content);
        $hasProfanity = $this->profanityFilter->containsProfanity($sanitizedContent);

        $status = $hasProfanity ? TestimonialStatus::Rejected : TestimonialStatus::Pending;

        $testimonial = new Testimonial([
            'user_id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'city' => $user->city,
            'content' => $sanitizedContent,
            'score' => $this->normalizeScore($data['score'] ?? null),
            'status' => $status,
            'ip' => $this->request->ip(),
            'user_agent' => substr((string) $this->request->userAgent(), 0, 250),
            'rejection_reason' => $hasProfanity ? 'profanity_auto' : null,
            'rejected_at' => $hasProfanity ? now() : null,
        ]);

        $testimonial->save();

        return $testimonial;
    }

    public function submitComment(User $user, Post $post, array $data): PostComment
    {
        $this->guardAgainstBan($user);
        $this->guardAgainstSpam($user, 'comment');

        if (! $post->commentsEnabled()) {
            abort(403, 'Comentários desabilitados para este post.');
        }

        $content = (string) ($data['content'] ?? '');
        $sanitizedContent = app(ContentSanitizer::class)->sanitize($content);
        $hasProfanity = $this->profanityFilter->containsProfanity($sanitizedContent);

        $status = $hasProfanity ? PostCommentStatus::Rejected : PostCommentStatus::Pending;

        $comment = new PostComment([
            'post_id' => $post->getKey(),
            'user_id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'content' => $sanitizedContent,
            'score' => $this->normalizeScore($data['score'] ?? null),
            'status' => $status,
            'ip' => $this->request->ip(),
            'user_agent' => substr((string) $this->request->userAgent(), 0, 250),
            'rejection_reason' => $hasProfanity ? 'profanity_auto' : null,
            'rejected_at' => $hasProfanity ? now() : null,
        ]);

        $comment->save();

        $this->invalidatePostRatingCache($post);

        return $comment;
    }

    public function approve(Testimonial|PostComment $record): void
    {
        $record->status = $record instanceof Testimonial
            ? TestimonialStatus::Approved
            : PostCommentStatus::Approved;
        $record->approved_at = now();
        $record->rejected_at = null;
        $record->rejection_reason = null;
        $record->save();

        if ($record instanceof PostComment) {
            $post = $record->post()->first();
            if ($post instanceof Post) {
                $this->invalidatePostRatingCache($post);
            }
        }
    }

    public function reject(Testimonial|PostComment $record, ?string $reason = null): void
    {
        $record->status = $record instanceof Testimonial
            ? TestimonialStatus::Rejected
            : PostCommentStatus::Rejected;
        $record->rejected_at = now();
        $record->approved_at = null;
        $record->rejection_reason = $reason ?? 'manual';
        $record->save();

        if ($record instanceof PostComment) {
            $post = $record->post()->first();
            if ($post instanceof Post) {
                $this->invalidatePostRatingCache($post);
            }
        }
    }

    public function rejectPendingByUser(User $user, string $reason = 'user_banned'): int
    {
        $testimonials = Testimonial::query()
            ->where('user_id', $user->getKey())
            ->where('status', TestimonialStatus::Pending->value)
            ->get();

        foreach ($testimonials as $testimonial) {
            $this->reject($testimonial, $reason);
        }

        $comments = PostComment::query()
            ->where('user_id', $user->getKey())
            ->where('status', PostCommentStatus::Pending->value)
            ->get();

        foreach ($comments as $comment) {
            $this->reject($comment, $reason);
        }

        return $testimonials->count() + $comments->count();
    }

    private function guardAgainstBan(User $user): void
    {
        if ($user->isBanned()) {
            abort(403, 'Sua conta está bloqueada.');
        }
    }

    private function guardAgainstSpam(User $user, string $channel): void
    {
        $key = sprintf('engagement:%s:%s', $channel, $user->getKey());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429, 'Muitas tentativas. Aguarde alguns minutos.');
        }

        RateLimiter::hit($key, 600);
    }

    private function normalizeScore(mixed $score): int
    {
        $value = (int) $score;

        return max(1, min(5, $value));
    }

    private function invalidatePostRatingCache(Post $post): void
    {
        Cache::forget($this->ratingCacheKey($post));
    }

    public function ratingCacheKey(Post $post): string
    {
        return sprintf('post:%s:rating', $post->getKey());
    }
}
