<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('catalog_category_id')
                ->nullable()
                ->constrained('catalog_categories')
                ->nullOnDelete();
            $table->foreignUuid('brand_id')
                ->nullable()
                ->constrained('brands')
                ->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('internal_code')->nullable();
            $table->string('sku')->nullable()->index();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            $table->text('features')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('promotional_price', 12, 2)->nullable();
            $table->string('price_display_mode', 20)->default('show');
            $table->string('currency', 3)->default('BRL');
            $table->string('unit', 20)->nullable();
            $table->string('availability', 20)->default('available');
            $table->unsignedInteger('informational_stock')->nullable();
            $table->string('inquiry_button_text', 100)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['status', 'is_featured']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
