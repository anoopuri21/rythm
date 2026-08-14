<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('featured_rank')->nullable()->after('is_featured');
            $table->boolean('is_trending')->default(false)->after('featured_rank');

            $table->index('is_trending');
            $table->index('featured_rank');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_trending']);
            $table->dropIndex(['featured_rank']);
            $table->dropColumn(['featured_rank', 'is_trending']);
        });
    }
};
