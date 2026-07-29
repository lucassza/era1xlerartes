<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\PostCommentStatus;
use App\Enums\TestimonialStatus;
use App\Models\BlogSetting;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AdminDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_testimonials_count_is_correct(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        BlogSetting::query()->create(BlogSetting::defaults());

        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        Testimonial::factory()->forUser($user)->pending()->count(2)->create();
        Testimonial::factory()->forUser($user)->approved()->count(3)->create();

        $count = Testimonial::query()
            ->where('status', TestimonialStatus::Pending->value)
            ->count();

        $this->assertSame(2, $count);
    }

    public function test_pending_comments_count_is_correct(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        BlogSetting::query()->create(BlogSetting::defaults());

        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        PostComment::factory()->forUser($user)->pending()->count(3)->create();
        PostComment::factory()->forUser($user)->approved()->count(1)->create();

        $count = PostComment::query()
            ->where('status', PostCommentStatus::Pending->value)
            ->count();

        $this->assertSame(3, $count);
    }

    public function test_top_rated_posts_query_includes_only_approved_ratings(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        BlogSetting::query()->create(BlogSetting::defaults());

        $user = User::factory()->siteUser()->create(['email_verified_at' => now()]);
        $post = Post::factory()->published()->create();

        PostComment::factory()->forPost($post)->forUser($user)->approved()->create(['score' => 5]);
        PostComment::factory()->forPost($post)->forUser($user)->approved()->create(['score' => 3]);
        PostComment::factory()->forPost($post)->forUser($user)->pending()->create(['score' => 1]);

        $posts = Post::query()
            ->published()
            ->whereHas('approvedComments')
            ->withAvg('approvedComments as average_rating', 'score')
            ->withCount('approvedComments as ratings_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('ratings_count')
            ->limit(5)
            ->get();

        $this->assertCount(1, $posts);
        $this->assertSame($post->id, $posts->first()->id);
        $this->assertSame(2, (int) $posts->first()->ratings_count);
        $this->assertSame(4.0, round((float) $posts->first()->average_rating, 1));
    }

    public function test_recent_activity_logs_returns_latest(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        activity()->causedBy($user)->log('test.event');

        $records = Activity::query()
            ->latest('created_at')
            ->limit(10)
            ->get();

        $this->assertGreaterThan(0, $records->count());
        $this->assertSame('created', $records->first()->event);
    }
}
