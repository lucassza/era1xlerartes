<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('source_platform')->nullable()->after('archived_at');
            $table->string('source_url')->nullable()->unique()->after('source_platform');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['source_url']);
            $table->dropColumn(['source_platform', 'source_url']);
        });
    }
};
