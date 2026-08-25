<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateReviews = DB::table('reviews')
            ->select(['product_id', 'user_id'])
            ->whereNotNull('user_id')
            ->groupBy('product_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateReviews) {
            throw new RuntimeException('Duplicate customer/product reviews must be resolved before applying the Phase 5 uniqueness constraint.');
        }

        Schema::table('reviews', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->after('is_approved');
            $table->text('merchant_reply')->nullable()->after('status');
            $table->foreignId('moderated_by')->nullable()->after('merchant_reply')->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
            $table->foreignId('replied_by')->nullable()->after('moderated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable()->after('replied_by');
            $table->unique(['product_id', 'user_id'], 'reviews_product_user_unique');
            $table->index(['product_id', 'status'], 'reviews_product_status_index');
        });

        DB::table('reviews')->where('is_approved', true)->update(['status' => 'approved']);

        Schema::create('product_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->string('status', 20)->default('pending');
            $table->text('answer')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status'], 'product_questions_product_status_index');
            $table->index(['user_id', 'created_at'], 'product_questions_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_questions');

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropUnique('reviews_product_user_unique');
            $table->dropIndex('reviews_product_status_index');
            $table->dropConstrainedForeignId('moderated_by');
            $table->dropConstrainedForeignId('replied_by');
            $table->dropColumn(['status', 'merchant_reply', 'moderated_at', 'replied_at']);
        });
    }
};
