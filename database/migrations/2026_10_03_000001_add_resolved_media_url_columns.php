<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Denormalised media URLs (see docs/media-architecture.md → M-7).
 *
 * Each media-bearing model stores the resolved URL(s) of its media collections
 * here, so the storefront and the admin panel read one stored string instead of
 * resolving through the media table on every render. Media Library stays the
 * writer of truth; App\Observers\MediaUrlObserver keeps these columns in step
 * and `php artisan media:sync-urls` backfills/repairs them.
 *
 * Values are host-relative (`/storage/...`), never absolute — the M-2 contract.
 * All columns are nullable: a NULL means "not resolved yet" and the accessors
 * fall back to Media Library, so this migration is safe to apply before the
 * backfill runs (or on a fresh install with no media at all).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('gallery_urls')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('og_image_url')->nullable();
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->json('gallery_urls')->nullable();
            $table->string('thumbnail_url')->nullable();
        });

        Schema::table('brands', function (Blueprint $table): void {
            $table->string('logo_url')->nullable();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('icon_url')->nullable();
        });

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->string('desktop_image_url')->nullable();
            $table->string('mobile_image_url')->nullable();
        });

        Schema::table('homepage_blocks', function (Blueprint $table): void {
            $table->string('image_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['gallery_urls', 'thumbnail_url', 'og_image_url']);
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn(['gallery_urls', 'thumbnail_url']);
        });

        Schema::table('brands', function (Blueprint $table): void {
            $table->dropColumn('logo_url');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('icon_url');
        });

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropColumn(['desktop_image_url', 'mobile_image_url']);
        });

        Schema::table('homepage_blocks', function (Blueprint $table): void {
            $table->dropColumn('image_url');
        });
    }
};
