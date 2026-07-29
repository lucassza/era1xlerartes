<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\InquiryStatus;
use App\Enums\ProductStatus;
use App\Mail\ProductInquiryAdminNotification;
use App\Mail\ProductInquiryVisitorConfirmation;
use App\Models\CatalogSetting;
use App\Models\Product;
use App\Models\ProductInquiry;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductInquirySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            PreventRequestForgery::class,
        ]);
        Mail::fake();
        Cache::flush();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '+55 11 99999 9999',
            'company' => 'Empresa X',
            'city' => 'São Paulo',
            'state' => 'SP',
            'subject' => 'Interesse no produto',
            'message' => 'Olá, gostaria de mais informações sobre este produto.',
            'consent' => '1',
            'website' => '',
            'product_id' => $product->id,
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
        ], $overrides);
    }

    public function test_valid_inquiry_is_saved_with_snapshot_and_history(): void
    {
        $product = Product::factory()->published()->create(['name' => 'Sofá Oslo']);
        CatalogSetting::current()->fill([
            'inquiry_recipients' => 'comercial@example.com, vendas@example.com',
        ])->save();

        $payload = $this->validPayload($product);
        $response = $this->post(route('products.inquiries.store', $product->slug), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('inquiry_status', 'ok');

        $inquiry = ProductInquiry::query()->latest('id')->first();
        $this->assertNotNull($inquiry);
        $this->assertSame($product->id, $inquiry->getAttribute('product_id'));
        $this->assertSame('Sofá Oslo', $inquiry->getAttribute('product_name'));
        $this->assertSame('Maria Silva', $inquiry->getAttribute('name'));
        $this->assertSame('maria@example.com', $inquiry->getAttribute('email'));
        $this->assertSame(InquiryStatus::New, $inquiry->getAttribute('status'));

        $this->assertDatabaseHas('product_inquiry_status_histories', [
            'product_inquiry_id' => $inquiry->id,
            'to_status' => InquiryStatus::New->value,
        ]);
    }

    public function test_product_name_snapshot_is_preserved_after_deletion(): void
    {
        $product = Product::factory()->published()->create(['name' => 'Cadeira Aço']);

        $this->post(
            route('products.inquiries.store', $product->slug),
            $this->validPayload($product),
        )->assertRedirect();

        $product->delete();

        $inquiry = ProductInquiry::query()->latest('id')->first();
        $this->assertNotNull($inquiry);
        $this->assertNull($inquiry->getAttribute('product_id'));
        $this->assertSame('Cadeira Aço', $inquiry->getAttribute('product_name'));
    }

    public function test_honeypot_rejects_submissions(): void
    {
        $product = Product::factory()->published()->create();

        $response = $this->post(
            route('products.inquiries.store', $product->slug),
            $this->validPayload($product, ['website' => 'spam-bot']),
        );

        $response->assertRedirect();
        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('product_inquiries', 0);
    }

    public function test_consent_must_be_accepted(): void
    {
        $product = Product::factory()->published()->create();

        $response = $this->post(
            route('products.inquiries.store', $product->slug),
            $this->validPayload($product, ['consent' => '0']),
        );

        $response->assertSessionHasErrors('consent');
        $this->assertDatabaseCount('product_inquiries', 0);
    }

    public function test_message_below_minimum_is_rejected(): void
    {
        $product = Product::factory()->published()->create();

        $response = $this->post(
            route('products.inquiries.store', $product->slug),
            $this->validPayload($product, ['message' => 'oi']),
        );

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('product_inquiries', 0);
    }

    public function test_inquiries_for_unknown_slug_return_404(): void
    {
        $response = $this->post(
            route('products.inquiries.store', 'slug-inexistente'),
            $this->validPayload(Product::factory()->published()->make()),
        );

        $response->assertNotFound();
    }

    public function test_draft_products_block_inquiries(): void
    {
        $draft = Product::factory()->create([
            'name' => 'Rascunho',
            'status' => ProductStatus::Draft,
        ]);

        $response = $this->post(
            route('products.inquiries.store', $draft->slug),
            $this->validPayload($draft),
        );

        $response->assertNotFound();
        $this->assertDatabaseCount('product_inquiries', 0);
    }

    public function test_rate_limit_blocks_after_five_submissions(): void
    {
        $product = Product::factory()->published()->create();
        CatalogSetting::current()->fill(['inquiry_recipients' => 'com@example.com'])->save();

        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('products.inquiries.store', $product->slug), $this->validPayload($product))
                ->assertRedirect();
        }

        $sixth = $this->post(route('products.inquiries.store', $product->slug), $this->validPayload($product));
        $sixth->assertRedirect();
        $sixth->assertSessionHasErrors('rate');

        $this->assertSame(5, ProductInquiry::query()->count());
    }

    public function test_inquiry_queues_admin_and_visitor_emails(): void
    {
        Mail::fake();

        $product = Product::factory()->published()->create();
        CatalogSetting::current()->fill(['inquiry_recipients' => 'com@example.com'])->save();

        $this->post(route('products.inquiries.store', $product->slug), $this->validPayload($product))
            ->assertRedirect();

        Mail::assertQueued(ProductInquiryAdminNotification::class, 1);
        Mail::assertQueued(ProductInquiryVisitorConfirmation::class, 1);
    }

    public function test_no_admin_email_queued_when_no_recipients(): void
    {
        Mail::fake();

        $product = Product::factory()->published()->create();
        CatalogSetting::current()->fill(['inquiry_recipients' => null])->save();

        $this->post(route('products.inquiries.store', $product->slug), $this->validPayload($product))
            ->assertRedirect();

        Mail::assertQueued(ProductInquiryAdminNotification::class, 0);
        Mail::assertQueued(ProductInquiryVisitorConfirmation::class, 1);
    }

    public function test_email_failure_does_not_lose_inquiry(): void
    {
        Mail::shouldReceive('to->queue')->andThrow(new \RuntimeException('SMTP indisponível'));

        $product = Product::factory()->published()->create();
        CatalogSetting::current()->fill(['inquiry_recipients' => 'com@example.com'])->save();

        $response = $this->post(route('products.inquiries.store', $product->slug), $this->validPayload($product));

        $response->assertRedirect();
        $response->assertSessionHas('inquiry_status', 'ok');
        $this->assertSame(1, ProductInquiry::query()->count());
    }

    public function test_json_accept_returns_201_with_payload(): void
    {
        Mail::fake();

        $product = Product::factory()->published()->create();

        $response = $this->postJson(
            route('products.inquiries.store', $product->slug),
            $this->validPayload($product),
        );

        $response->assertCreated();
        $response->assertJsonStructure(['status', 'message', 'inquiry_id']);
    }
}
