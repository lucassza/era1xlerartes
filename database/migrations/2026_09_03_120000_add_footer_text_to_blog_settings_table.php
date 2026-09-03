<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FOOTER_TEXT = 'Catálogo de produtos com histórias, novidades e conteúdo editorial.';

    public function up(): void
    {
        Schema::table('blog_settings', function (Blueprint $table): void {
            $table->text('footer_text')->nullable()->default(self::FOOTER_TEXT)->after('site_description');
        });

        DB::table('blog_settings')->update([
            'footer_text' => $this->currentFooterText(),
        ]);
    }

    public function down(): void
    {
        Schema::table('blog_settings', function (Blueprint $table): void {
            $table->dropColumn('footer_text');
        });
    }

    private function currentFooterText(): string
    {
        if (! Schema::hasTable('catalog_settings')) {
            return self::FOOTER_TEXT;
        }

        $introText = DB::table('catalog_settings')->orderBy('id')->value('intro_text');

        return filled($introText) ? (string) $introText : self::FOOTER_TEXT;
    }
};
