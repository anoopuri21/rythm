<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Generic repeater blocks for homepage sections:
        // section_key ∈ testimonial|story|ugc|number|usp|promo|comparison
        Schema::create('homepage_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 40);
            $table->string('title', 255)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['section_key', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_blocks');
    }
};
