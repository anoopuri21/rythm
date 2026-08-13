<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_entries', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // pages, products, categories, blog posts…
            $table->string('meta_title', 120)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('og_title', 120)->nullable();
            $table->string('og_description', 300)->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->json('schema_json')->nullable();
            $table->text('head_scripts')->nullable();
            $table->string('robots', 100)->nullable();
            $table->timestamps();

            // morphs() already creates the (seoable_type, seoable_id) index.
            $table->unique(['seoable_type', 'seoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_entries');
    }
};
