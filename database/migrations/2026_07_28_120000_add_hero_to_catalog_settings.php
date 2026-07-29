<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_settings', function (Blueprint $table): void {
            $table->boolean('hero_enabled')->default(false)->after('banner');
            $table->string('hero_title')->nullable()->after('hero_enabled');
            $table->text('hero_subtitle')->nullable()->after('hero_title');
            $table->string('hero_cta_label', 100)->nullable()->after('hero_subtitle');
            $table->string('hero_cta_url')->nullable()->after('hero_cta_label');
            $table->string('hero_image_path')->nullable()->after('hero_cta_url');
            $table->string('hero_alignment', 20)->default('left')->after('hero_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'hero_enabled',
                'hero_title',
                'hero_subtitle',
                'hero_cta_label',
                'hero_cta_url',
                'hero_image_path',
                'hero_alignment',
            ]);
        });
    }
};
