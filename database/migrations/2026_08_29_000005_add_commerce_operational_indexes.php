<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['user_id', 'placed_at', 'id'], 'orders_customer_timeline_idx');
            $table->index(['payment_status', 'placed_at', 'id'], 'orders_payment_operations_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['order_id', 'status', 'created_at'], 'payments_order_state_idx');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->index(['product_id', 'is_active', 'stock'], 'variants_product_stock_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', fn (Blueprint $table) => $table->dropIndex('variants_product_stock_idx'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('payments_order_state_idx'));
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_customer_timeline_idx');
            $table->dropIndex('orders_payment_operations_idx');
        });
    }
};
