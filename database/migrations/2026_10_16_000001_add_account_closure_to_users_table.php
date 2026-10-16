<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('deactivated_at')->nullable()->after('email_verified_at');
            $table->timestamp('removed_at')->nullable()->after('deactivated_at');
            $table->string('account_closure_token', 64)->nullable()->after('remember_token');
            $table->timestamp('account_closure_expires_at')->nullable()->after('account_closure_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'deactivated_at',
                'removed_at',
                'account_closure_token',
                'account_closure_expires_at',
            ]);
        });
    }
};
