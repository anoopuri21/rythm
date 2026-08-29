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
            $table->string('idempotency_key', 100)->nullable()->after('order_number')->unique();
        });

        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 40);
            $table->string('gateway_event_id', 150);
            $table->string('event_type', 100);
            $table->string('status', 30)->default('received');
            $table->char('payload_hash', 64);
            $table->json('redacted_metadata')->nullable();
            $table->string('failure_message', 500)->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_event_id'], 'payment_events_gateway_event_unique');
            $table->index(['status', 'received_at'], 'payment_events_status_time_idx');
            $table->index(['order_id', 'event_type'], 'payment_events_order_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
