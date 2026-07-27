<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('attributes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('attribute_group_id')
                ->constrained('attribute_groups')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 30)->default('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attribute_group_id', 'slug']);
            $table->index(['attribute_group_id', 'sort_order']);
        });

        Schema::create('attribute_group_catalog_category', function (Blueprint $table): void {
            $table->foreignUuid('attribute_group_id')
                ->constrained('attribute_groups')
                ->cascadeOnDelete();
            $table->foreignUuid('catalog_category_id')
                ->constrained('catalog_categories')
                ->cascadeOnDelete();
            $table->primary(['attribute_group_id', 'catalog_category_id']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignUuid('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_group_catalog_category');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_groups');
    }
};
