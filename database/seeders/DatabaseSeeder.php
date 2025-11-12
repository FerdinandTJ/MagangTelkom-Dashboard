<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        // Seed master data in order of dependencies
        // Note: Regions are auto-seeded in migration 2025_11_12_000004
        $this->call([
            WitelSeeder::class,              // Depends on: regions (auto-seeded)
            AccountManagerSeeder::class,     // Depends on: witels
            DummyDataSeeder::class,          // Insert dummy data for testing
        ]);
    }
}
