<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('cgst_amount_snapshot', 12, 2)->nullable()->after('tax_amount_snapshot');
            $table->decimal('sgst_amount_snapshot', 12, 2)->nullable()->after('cgst_amount_snapshot');
            $table->decimal('igst_amount_snapshot', 12, 2)->nullable()->after('sgst_amount_snapshot');
            $table->string('gst_supply_type_snapshot', 16)->nullable()->after('igst_amount_snapshot');
            $table->string('tax_origin_region_snapshot', 100)->nullable()->after('tax_destination_region_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'cgst_amount_snapshot',
                'sgst_amount_snapshot',
                'igst_amount_snapshot',
                'gst_supply_type_snapshot',
                'tax_origin_region_snapshot',
            ]);
        });
    }
};
