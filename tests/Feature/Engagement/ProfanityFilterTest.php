<?php

declare(strict_types=1);

namespace Tests\Feature\Engagement;

use App\Enums\PostCommentStatus;
use App\Enums\TestimonialStatus;
use App\Models\BlogSetting;
use App\Models\Post;
use App\Models\User;
use App\Services\EngagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfanityFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_content_is_accepted(): void
    {
        $user = User::factory()->siteUser()->create();

        $testimonial = app(EngagementService::class)->submitTestimonial($user, [
            'content' => 'Adoro o blog e suas histórias inspiradoras sobre miniaturas.',
            'score' => 5,
        ]);

        $this->assertSame(TestimonialStatus::Pending, $testimonial->status);
        $this->assertNull($testimonial->rejection_reason);
    }

    public function test_profanity_is_auto_rejected_for_testimonial(): void
    {
        $user = User::factory()->siteUser()->create();

        $testimonial = app(EngagementService::class)->submitTestimonial($user, [
            'content' => 'Que post mais porra, não gostei do conteúdo publicado aqui.',
            'score' => 2,
        ]);

        $this->assertSame(TestimonialStatus::Rejected, $testimonial->status);
        $this->assertSame('profanity_auto', $testimonial->rejection_reason);
        $this->assertNotNull($testimonial->rejected_at);
    }

    public function test_profanity_is_auto_rejected_for_comment(): void
    {
        BlogSetting::query()->create(BlogSetting::defaults() + ['comments_enabled' => true]);
        $user = User::factory()->siteUser()->create();
        $post = Post::factory()->published()->create();

        $comment = app(EngagementService::class)->submitComment($user, $post, [
            'content' => 'Que post mais merda, fiquei indignado com isso.',
            'score' => 1,
        ]);

        $this->assertSame(PostCommentStatus::Rejected, $comment->status);
        $this->assertSame('profanity_auto', $comment->rejection_reason);
    }

    public function test_partial_words_do_not_trigger_filter(): void
    {
        $user = User::factory()->siteUser()->create();

        $testimonial = app(EngagementService::class)->submitTestimonial($user, [
            'content' => 'A análise do cenário foi brilhante, com detalhes minuciosos.',
            'score' => 5,
        ]);

        $this->assertSame(TestimonialStatus::Pending, $testimonial->status);
    }

    public function test_profanity_filter_can_be_disabled_via_config(): void
    {
        config()->set('profanity.enabled', false);

        $user = User::factory()->siteUser()->create();

        $testimonial = app(EngagementService::class)->submitTestimonial($user, [
            'content' => 'Isso é uma porcaria completa, mas o filtro está desligado.',
            'score' => 2,
        ]);

        $this->assertSame(TestimonialStatus::Pending, $testimonial->status);
    }
}
