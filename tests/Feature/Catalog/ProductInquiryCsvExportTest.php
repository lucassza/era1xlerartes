<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Filament\Exports\ProductInquiryCsvExporter;
use App\Models\ProductInquiry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductInquiryCsvExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_csv_export_includes_header_and_rows_with_utf8_bom(): void
    {
        $inquiry = ProductInquiry::factory()->create([
            'product_name' => 'Sofá Oslo',
            'name' => 'Maria',
            'email' => 'maria@example.com',
        ]);

        $response = ProductInquiryCsvExporter::stream(ProductInquiry::query()->with(['product', 'assignee'])->get()->all());

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Sofá Oslo', $content);
        $this->assertStringContainsString('Maria', $content);
        $this->assertStringContainsString('maria@example.com', $content);
        $this->assertStringContainsString($inquiry->id, $content);
    }
}
