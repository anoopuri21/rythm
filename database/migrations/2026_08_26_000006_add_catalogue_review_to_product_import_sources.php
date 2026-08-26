<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_import_sources', function (Blueprint $table): void {
            $table->boolean('publication_review_required')->default(true)->after('payload_hash');
            $table->json('publication_review_reasons')->nullable()->after('publication_review_required');
            $table->timestamp('publication_reviewed_at')->nullable()->after('publication_review_reasons');
            $table->foreignId('publication_reviewed_by')->nullable()->after('publication_reviewed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('commercial_use_approved_at')->nullable()->after('publication_reviewed_by');
            $table->foreignId('commercial_use_approved_by')->nullable()->after('commercial_use_approved_at')->constrained('users')->nullOnDelete();
            $table->index(['publication_review_required', 'publication_reviewed_at'], 'product_import_review_queue_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_import_sources', function (Blueprint $table): void {
            $table->dropIndex('product_import_review_queue_idx');
            $table->dropConstrainedForeignId('commercial_use_approved_by');
            $table->dropColumn('commercial_use_approved_at');
            $table->dropConstrainedForeignId('publication_reviewed_by');
            $table->dropColumn(['publication_reviewed_at', 'publication_review_reasons', 'publication_review_required']);
        });
    }
};
