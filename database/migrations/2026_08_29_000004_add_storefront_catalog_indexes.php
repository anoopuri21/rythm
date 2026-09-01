<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->index(['is_active', 'price', 'id'], 'products_storefront_price_idx');
            $table->index(['is_active', 'stock', 'id'], 'products_storefront_stock_idx');
            $table->index(['is_active', 'created_at', 'id'], 'products_storefront_newest_idx');
            $table->index(['is_active', 'is_featured', 'id'], 'products_storefront_featured_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_storefront_price_idx');
            $table->dropIndex('products_storefront_stock_idx');
            $table->dropIndex('products_storefront_newest_idx');
            $table->dropIndex('products_storefront_featured_idx');
        });
    }
};
