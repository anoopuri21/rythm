<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL may use the unique order_id index to support the foreign key.
        // Add a replacement index first so dropping uniqueness never invalidates the FK.
        Schema::table('refunds', function (Blueprint $table): void {
            $table->index('order_id', 'refunds_order_id_idx');
        });

        Schema::table('refunds', function (Blueprint $table): void {
            $table->dropUnique('refunds_order_id_unique');
            $table->string('idempotency_key', 100)->nullable()->unique()->after('payment_id');
            $table->foreignId('requested_by')->nullable()->after('failure_message')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('processed_at')->nullable()->after('approved_at');
            $table->index(['payment_id', 'status'], 'refunds_payment_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table): void {
            $table->dropIndex('refunds_payment_status_idx');
            $table->dropUnique(['idempotency_key']);
            $table->dropConstrainedForeignId('requested_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['idempotency_key', 'approved_at', 'processed_at']);
            $table->unique('order_id');
        });

        Schema::table('refunds', function (Blueprint $table): void {
            $table->dropIndex('refunds_order_id_idx');
        });
    }
};
