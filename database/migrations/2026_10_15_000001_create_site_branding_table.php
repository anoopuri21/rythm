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
        Schema::create('site_branding', function (Blueprint $table) {
            $table->id();
            $table->string('navbar_logo_url')->nullable();
            $table->string('footer_logo_url')->nullable();
            $table->timestamps();
        });

        DB::table('site_branding')->insert([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_branding');
    }
};
