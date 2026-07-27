<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\InquiryStatus;
use App\Models\ProductInquiry;
use App\Models\User;
use App\Services\ProductInquiryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductInquiryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_mark_as_viewed_only_runs_once(): void
    {
        $inquiry = ProductInquiry::factory()->create();
        $service = app(ProductInquiryService::class);

        $this->assertTrue($service->markAsViewed($inquiry));
        $inquiry->refresh();

        $this->assertNotNull($inquiry->getAttribute('first_viewed_at'));
        $this->assertSame(InquiryStatus::Viewed, $inquiry->getAttribute('status'));
        $this->assertFalse($service->markAsViewed($inquiry));
    }

    public function test_change_status_creates_history_and_updates_milestones(): void
    {
        $inquiry = ProductInquiry::factory()->create();
        $service = app(ProductInquiryService::class);
        $user = User::factory()->admin()->create();

        $service->changeStatus($inquiry, InquiryStatus::InService, $user, 'início');

        $inquiry->refresh();
        $this->assertSame(InquiryStatus::InService, $inquiry->getAttribute('status'));
        $this->assertNotNull($inquiry->getAttribute('first_answered_at'));
        $this->assertDatabaseCount('product_inquiry_status_histories', 1);
    }

    public function test_complete_status_records_completion_timestamp(): void
    {
        $inquiry = ProductInquiry::factory()->create();
        $service = app(ProductInquiryService::class);

        $service->changeStatus($inquiry, InquiryStatus::Completed, null, 'ok');

        $inquiry->refresh();
        $this->assertNotNull($inquiry->getAttribute('completed_at'));
        $this->assertSame(InquiryStatus::Completed, $inquiry->getAttribute('status'));
    }

    public function test_assign_logs_history_with_actor(): void
    {
        $inquiry = ProductInquiry::factory()->create();
        $service = app(ProductInquiryService::class);
        $actor = User::factory()->admin()->create();
        $assignee = User::factory()->admin()->create();

        $service->assign($inquiry, $assignee, $actor);

        $inquiry->refresh();
        $this->assertSame($assignee->id, $inquiry->getAttribute('assignee_id'));
        $this->assertDatabaseHas('product_inquiry_status_histories', [
            'product_inquiry_id' => $inquiry->id,
            'user_id' => $actor->id,
        ]);
    }

    public function test_dashboard_stats_aggregates_counts(): void
    {
        ProductInquiry::factory()->count(2)->create(['status' => InquiryStatus::New]);
        ProductInquiry::factory()->count(3)->create(['status' => InquiryStatus::InService]);
        ProductInquiry::factory()->count(1)->create(['status' => InquiryStatus::Completed]);

        $stats = app(ProductInquiryService::class)->dashboardStats();

        $this->assertSame(2, $stats['new']);
        $this->assertSame(3, $stats['in_service']);
        $this->assertSame(1, $stats['completed']);
        $this->assertSame(6, $stats['total']);
        $this->assertSame(16.7, $stats['completion_rate']);
    }
}
