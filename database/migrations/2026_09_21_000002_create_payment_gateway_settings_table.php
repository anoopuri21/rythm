<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('driver', 40)->unique();
            $table->string('mode', 16)->default('test');
            $table->string('test_key_id')->nullable();
            $table->text('test_key_secret')->nullable();
            $table->text('test_webhook_secret')->nullable();
            $table->string('live_key_id')->nullable();
            $table->text('live_key_secret')->nullable();
            $table->text('live_webhook_secret')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
