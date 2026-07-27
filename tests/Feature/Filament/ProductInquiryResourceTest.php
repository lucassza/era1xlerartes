<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\InquiryStatus;
use App\Models\ProductInquiry;
use App\Models\User;
use App\Services\ProductInquiryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductInquiryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_editor_can_view_inquiries_index(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Editor');

        $response = $this->actingAs($user)->get('/admin/product-inquiries');

        $response->assertOk();
    }

    public function test_author_is_blocked_from_inquiries_index(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Author');

        $response = $this->actingAs($user)->get('/admin/product-inquiries');

        $response->assertForbidden();
    }

    public function test_mark_as_viewed_transitions_new_inquiry_to_viewed(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('Super Admin');

        $inquiry = ProductInquiry::factory()->create();
        $this->actingAs($user);

        app(ProductInquiryService::class)->markAsViewed($inquiry, $user);

        $inquiry->refresh();
        $this->assertNotNull($inquiry->getAttribute('first_viewed_at'));
        $this->assertSame(InquiryStatus::Viewed, $inquiry->getAttribute('status'));
    }
}
