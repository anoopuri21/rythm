<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Homepage banner slots with a built-in default in code
        // (HeroBannerService::DEFAULTS) and an optional admin override row.
        Schema::create('hero_banners', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 40)->unique();
            $table->string('title', 160)->nullable();
            $table->string('subtitle', 200)->nullable();
            $table->string('cta_label', 80)->nullable();
            $table->string('href', 300)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('alt', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_banners');
    }
};
