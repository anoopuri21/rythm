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
            $table->string('hsn_code', 20)->nullable()->after('sku')->index();
            $table->string('tax_classification', 80)->nullable()->after('hsn_code')->index();
            $table->decimal('tax_rate', 7, 4)->nullable()->after('tax_classification');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('hsn_code_snapshot', 20)->nullable()->after('sku');
            $table->string('tax_classification_snapshot', 80)->nullable()->after('hsn_code_snapshot');
            $table->decimal('tax_rate_snapshot', 7, 4)->nullable()->after('tax_classification_snapshot');
            $table->decimal('taxable_amount_snapshot', 12, 2)->nullable()->after('tax_rate_snapshot');
            $table->decimal('tax_amount_snapshot', 12, 2)->default(0)->after('taxable_amount_snapshot');
            $table->boolean('tax_calculation_enabled_snapshot')->default(false)->after('tax_amount_snapshot');
            $table->string('tax_destination_region_snapshot', 120)->nullable()->after('tax_calculation_enabled_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'hsn_code_snapshot',
                'tax_classification_snapshot',
                'tax_rate_snapshot',
                'taxable_amount_snapshot',
                'tax_amount_snapshot',
                'tax_calculation_enabled_snapshot',
                'tax_destination_region_snapshot',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['hsn_code']);
            $table->dropIndex(['tax_classification']);
            $table->dropColumn(['hsn_code', 'tax_classification', 'tax_rate']);
        });
    }
};
