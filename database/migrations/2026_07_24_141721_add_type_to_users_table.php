<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('type')->default('site')->after('email');
            $table->string('city', 120)->nullable()->after('name');
            $table->timestampTz('banned_at')->nullable()->after('email_verified_at');

            $table->index('type');
            $table->index('banned_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['type']);
            $table->dropIndex(['banned_at']);
            $table->dropColumn(['type', 'city', 'banned_at']);
        });
    }
};
