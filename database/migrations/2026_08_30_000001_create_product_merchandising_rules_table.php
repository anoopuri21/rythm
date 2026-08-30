<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_merchandising_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('target_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('rule_type', 40);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['source_product_id', 'target_product_id', 'rule_type'],
                'product_merchandising_rules_unique',
            );
            $table->index(
                ['source_product_id', 'rule_type', 'is_active', 'priority'],
                'product_merchandising_source_idx',
            );
            $table->index(['target_product_id', 'rule_type'], 'product_merchandising_target_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_merchandising_rules');
    }
};
