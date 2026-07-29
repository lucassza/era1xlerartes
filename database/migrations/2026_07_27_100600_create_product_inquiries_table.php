<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_inquiries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->string('product_name');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('origin', 50)->nullable();
            $table->string('source_url')->nullable();
            $table->json('utm')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('status', 30)->default('new');
            $table->foreignUuid('assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('first_answered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['product_id', 'status']);
            $table->index('created_at');
            $table->index('assignee_id');
        });

        Schema::create('product_inquiry_status_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_inquiry_id')
                ->constrained('product_inquiries')
                ->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_inquiry_id');
            $table->index(['product_inquiry_id', 'created_at']);
        });

        Schema::create('product_inquiry_notes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_inquiry_id')
                ->constrained('product_inquiries')
                ->cascadeOnDelete();
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('note');
            $table->timestamps();

            $table->index('product_inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inquiry_notes');
        Schema::dropIfExists('product_inquiry_status_histories');
        Schema::dropIfExists('product_inquiries');
    }
};
