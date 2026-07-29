<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->default('Catálogo');
            $table->text('intro_text')->nullable();
            $table->json('banner')->nullable();
            $table->unsignedInteger('per_page')->default(12);
            $table->string('default_sort', 30)->default('recent');
            $table->boolean('show_unavailable')->default(true);
            $table->string('inquiry_button_text', 100)->default('Consulte');
            $table->text('inquiry_recipients')->nullable();
            $table->text('inquiry_confirmation_message')->nullable();
            $table->string('commercial_phone', 30)->nullable();
            $table->string('commercial_whatsapp', 30)->nullable();
            $table->boolean('captcha_enabled')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_settings');
    }
};
