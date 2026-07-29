<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_settings', function (Blueprint $table): void {
            $table->boolean('comments_enabled')->default(true)->after('newsletter_enabled');
            $table->boolean('testimonials_enabled')->default(true)->after('comments_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('blog_settings', function (Blueprint $table): void {
            $table->dropColumn(['comments_enabled', 'testimonials_enabled']);
        });
    }
};
