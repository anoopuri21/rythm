<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_blocks', function (Blueprint $table) {
            // Admin-selected glyph for USP / feature blocks (NO-HARDCODE rule:
            // the icon is data, the SVG path lives in components/ui/icon).
            $table->string('icon', 40)->nullable()->after('section_key');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_blocks', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
