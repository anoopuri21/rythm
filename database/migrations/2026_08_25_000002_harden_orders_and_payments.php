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
            $table->string('coupon_code', 50)->nullable()->after('discount')->index();
            $table->timestamp('coupon_usage_recorded_at')->nullable()->after('coupon_code');
            $table->timestamp('coupon_usage_released_at')->nullable()->after('coupon_usage_recorded_at');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->unique(['gateway', 'gateway_order_id'], 'payments_gateway_order_unique');
            $table->unique(['gateway', 'gateway_payment_id'], 'payments_gateway_payment_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_gateway_order_unique');
            $table->dropUnique('payments_gateway_payment_unique');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['coupon_code']);
            $table->dropColumn(['coupon_code', 'coupon_usage_recorded_at', 'coupon_usage_released_at']);
        });
    }
};
