<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Products keep their own image columns instead of pointing at the media table.
 *
 * `image`     — the main product image, as the root-relative URL of a file in
 *               public/uploads (e.g. /uploads/products/01J9ZQ….jpg).
 * `gallery`   — extra product images, same URL shape.
 * `og_image`  — optional social-share image; falls back to `image`.
 *
 * Existing Spatie media is copied into the new shape by
 * `php artisan product-images:migrate` (idempotent, see docs/media-architecture.md).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('image', 500)->nullable()->after('is_trending');
            $table->json('gallery')->nullable()->after('image');
            $table->string('og_image', 500)->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['image', 'gallery', 'og_image']);
        });
    }
};
