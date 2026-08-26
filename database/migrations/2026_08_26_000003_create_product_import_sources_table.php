<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_import_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('source', 50);
            $table->string('source_product_id', 100);
            $table->string('source_url', 2048);
            $table->char('payload_hash', 64);
            $table->timestamp('imported_at');
            $table->timestamps();

            $table->unique(['source', 'source_product_id'], 'product_import_source_identity_unique');
            $table->index(['source', 'payload_hash'], 'product_import_source_hash_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_import_sources');
    }
};
