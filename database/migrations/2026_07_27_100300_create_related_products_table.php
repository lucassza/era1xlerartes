<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('related_products', function (Blueprint $table): void {
            $table->uuid('product_id');
            $table->uuid('related_product_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();
            $table->foreign('related_product_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();

            $table->primary(['product_id', 'related_product_id']);
            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_products');
    }
};
