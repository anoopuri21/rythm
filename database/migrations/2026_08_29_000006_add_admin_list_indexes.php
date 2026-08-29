<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', fn (Blueprint $table) =>
            $table->index(['status', 'created_at', 'id'], 'contact_admin_queue_idx'));
        Schema::table('reviews', fn (Blueprint $table) =>
            $table->index(['status', 'is_approved', 'created_at'], 'reviews_admin_queue_idx'));
        Schema::table('product_questions', fn (Blueprint $table) =>
            $table->index(['status', 'created_at', 'id'], 'questions_admin_queue_idx'));
        Schema::table('shipments', fn (Blueprint $table) =>
            $table->index(['status', 'created_at', 'id'], 'shipments_admin_queue_idx'));
    }

    public function down(): void
    {
        Schema::table('shipments', fn (Blueprint $table) => $table->dropIndex('shipments_admin_queue_idx'));
        Schema::table('product_questions', fn (Blueprint $table) => $table->dropIndex('questions_admin_queue_idx'));
        Schema::table('reviews', fn (Blueprint $table) => $table->dropIndex('reviews_admin_queue_idx'));
        Schema::table('contact_messages', fn (Blueprint $table) => $table->dropIndex('contact_admin_queue_idx'));
    }
};
