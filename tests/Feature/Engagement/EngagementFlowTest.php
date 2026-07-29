<?php

declare(strict_types=1);

namespace Tests\Feature\Engagement;

use App\Enums\PostCommentStatus;
use App\Enums\TestimonialStatus;
use App\Models\BlogSetting;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\EngagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_verified_user_can_submit_testimonial_pending(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['testimonials_enabled' => true]));
        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->from(route('testimonials.index'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('testimonials.store'), [
                '_token' => 'test-token',
                'content' => 'Amei o post sobre dioramas, ficou maravilhoso e inspirador.',
                'score' => 5,
            ]);

        $response->assertRedirect(route('testimonials.index'));
        $this->assertDatabaseHas('testimonials', [
            'user_id' => $user->getKey(),
            'status' => TestimonialStatus::Pending->value,
            'score' => 5,
        ]);
    }

    public function test_guest_cannot_submit_testimonial(): void
    {
        $response = $this->withSession(['_token' => 'test-token'])
            ->post(route('testimonials.store'), [
                '_token' => 'test-token',
                'content' => 'Tentando enviar sem estar logado.',
                'score' => 5,
            ]);

        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_unverified_user_cannot_submit_testimonial(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['testimonials_enabled' => true]));
        $user = User::factory()->siteUser()->unverified()->create();

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('testimonials.store'), [
                '_token' => 'test-token',
                'content' => 'Conteúdo válido sem verificação de e-mail.',
                'score' => 5,
            ]);

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_banned_user_cannot_submit_testimonial(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['testimonials_enabled' => true]));
        $user = User::factory()->siteUser()->banned()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('testimonials.store'), [
                '_token' => 'test-token',
                'content' => 'Tentando enviar mesmo banido do sistema.',
                'score' => 5,
            ]);

        $response->assertStatus(403);
    }

    public function test_global_toggle_blocks_testimonial_index(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['testimonials_enabled' => false]));

        $response = $this->get(route('testimonials.index'));

        $response->assertNotFound();
    }

    public function test_global_toggle_blocks_testimonial_store(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['testimonials_enabled' => false]));
        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('testimonials.store'), [
                '_token' => 'test-token',
                'content' => 'Tentando enviar depoimentos com a flag global desabilitada.',
                'score' => 5,
            ]);

        $response->assertNotFound();
    }

    public function test_admin_can_approve_testimonial_and_show_in_index(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['testimonials_enabled' => true]));
        $author = User::factory()->siteUser()->create();
        $testimonial = Testimonial::factory()->forUser($author)->create([
            'status' => TestimonialStatus::Pending,
        ]);

        app(EngagementService::class)->approve($testimonial);

        $this->assertSame(TestimonialStatus::Approved, $testimonial->fresh()->status);
        $this->assertNotNull($testimonial->fresh()->approved_at);

        $response = $this->get(route('testimonials.index'));
        $response->assertOk();
        $response->assertSee($testimonial->fresh()->name);
    }

    public function test_user_can_submit_comment_when_post_allows(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['comments_enabled' => true]));
        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        $post = Post::factory()->published()->create();

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('posts.comments.store', $post), [
                '_token' => 'test-token',
                'content' => 'Comentário muito interessante sobre o post, parabéns!',
                'score' => 5,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('post_comments', [
            'post_id' => $post->getKey(),
            'user_id' => $user->getKey(),
            'status' => PostCommentStatus::Pending->value,
        ]);
    }

    public function test_user_cannot_comment_on_locked_post(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['comments_enabled' => true]));
        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        $post = Post::factory()->published()->create(['comments_locked' => true]);

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('posts.comments.store', $post), [
                '_token' => 'test-token',
                'content' => 'Tentando comentar em post bloqueado.',
                'score' => 4,
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('post_comments', 0);
    }

    public function test_global_comment_toggle_blocks_comment_submission(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['comments_enabled' => false]));
        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        $post = Post::factory()->published()->create();

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('posts.comments.store', $post), [
                '_token' => 'test-token',
                'content' => 'Tentando comentar com flag global desligada.',
                'score' => 4,
            ]);

        $response->assertNotFound();
    }

    public function test_post_average_rating_uses_only_approved_comments(): void
    {
        BlogSetting::query()->create(array_merge(BlogSetting::defaults(), ['comments_enabled' => true]));
        $post = Post::factory()->published()->create();

        PostComment::factory()->forPost($post)->approved()->create(['score' => 5]);
        PostComment::factory()->forPost($post)->approved()->create(['score' => 3]);
        PostComment::factory()->forPost($post)->pending()->create(['score' => 1]);

        $this->assertSame(4.0, $post->fresh()->averageRating());
        $this->assertSame(2, $post->fresh()->ratingsCount());
        $distribution = $post->fresh()->ratingDistribution();
        $this->assertSame(1, $distribution[5]);
        $this->assertSame(0, $distribution[4]);
        $this->assertSame(1, $distribution[3]);
    }

    public function test_banning_user_rejects_pending_submissions(): void
    {
        BlogSetting::query()->create(BlogSetting::defaults());
        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        $post = Post::factory()->published()->create();

        $testimonial = Testimonial::factory()->forUser($user)->pending()->create();
        $comment = PostComment::factory()->forUser($user)->forPost($post)->pending()->create();

        $user->update(['banned_at' => now()]);

        $this->assertSame(TestimonialStatus::Rejected, $testimonial->fresh()->status);
        $this->assertSame('user_banned', $testimonial->fresh()->rejection_reason);
        $this->assertSame(PostCommentStatus::Rejected, $comment->fresh()->status);
    }
}
