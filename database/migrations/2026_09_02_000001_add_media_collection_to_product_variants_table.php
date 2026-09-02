<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Table;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spatie Media Library creates the media table in its own migration
        // This migration just ensures the product_variants table exists for reference
        // The variant media will use Spatie's polymorphic media table
    }

    public function down(): void
    {
        // No structural changes needed - Spatie handles media polymorphically
    }
};
