<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Local-dev convenience users (idempotent — safe to re-run):
     *   admin@rythme.test / admin1234  → Filament admin panel login (/admin)
     *   test@example.com  / password   → generic test user
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@rythme.test'],
            ['name' => 'Rhythm Exports Admin', 'password' => bcrypt('admin1234')],
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')],
        );

        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
