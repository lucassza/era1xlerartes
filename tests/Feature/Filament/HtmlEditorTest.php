<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HtmlEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_form_renders_the_rich_text_editor(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin/posts/create')
            ->assertOk()
            ->assertSee('js/hugerte/hugerte-editor.js', false)
            ->assertSee('hugerteEditor(', false);
    }

    public function test_product_rich_text_fields_are_sanitized_on_save(): void
    {
        $product = Product::factory()->create([
            'description' => '<p>Texto</p><script>alert(1)</script>',
            'specifications' => '<ul><li onclick="x()">Item</li></ul>',
            'features' => '<p><a href="javascript:alert(1)">link</a></p>',
        ]);

        $product->refresh();

        $this->assertStringContainsString('<p>Texto</p>', $product->description);
        $this->assertStringNotContainsString('<script', $product->description);
        $this->assertStringNotContainsString('onclick', $product->specifications);
        $this->assertStringNotContainsString('javascript:', $product->features);
    }
}
