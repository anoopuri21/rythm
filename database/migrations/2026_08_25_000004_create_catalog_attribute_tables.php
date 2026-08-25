<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('type', 30)->default('select');
            $table->string('unit', 30)->nullable();
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_variant')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'is_filterable', 'sort_order'], 'product_attributes_filter_idx');
        });

        Schema::create('product_attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value', 150);
            $table->string('slug', 150);
            $table->string('color_hex', 7)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_attribute_id', 'slug'], 'attribute_values_slug_unique');
            $table->index(['product_attribute_id', 'sort_order'], 'attribute_values_sort_idx');
        });

        Schema::create('category_product_attribute', function (Blueprint $table): void {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->primary(['category_id', 'product_attribute_id'], 'category_attribute_pk');
            $table->index(['category_id', 'is_filterable', 'sort_order'], 'category_attribute_filter_idx');
        });

        Schema::create('product_attribute_value_product', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_attribute_value_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_id', 'product_attribute_value_id'], 'product_attribute_value_pk');
        });

        Schema::create('product_attribute_value_product_variant', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_attribute_value_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_variant_id', 'product_attribute_value_id'], 'variant_attribute_value_pk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_product_variant');
        Schema::dropIfExists('product_attribute_value_product');
        Schema::dropIfExists('category_product_attribute');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
    }
};
