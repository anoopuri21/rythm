<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Product Q&A feature removed from storefront and admin.
 * Historical migration 2026_08_26_000001 still creates the table on fresh installs;
 * this migration drops it so end state has no product_questions.
 * Does not touch reviews (same historical migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_questions');
    }

    public function down(): void
    {
        // Intentionally empty: Q&A is retired. Re-introduce only via a new feature migration.
    }
};
