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

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User 1',
                'username' => 'admin1',
                'password' => 'admin123',
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User 2',
                'username' => 'admin2',
                'password' => 'admin123',
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );

        // Create viewer user for testing
        User::firstOrCreate(
            ['email' => 'viewer@gmail.com'],
            [
                'name' => 'User1',
                'username' => 'telkom1',
                'password' => 'password123',
                'email_verified_at' => now(),
                'role' => 'viewer',
            ]
        );

        // Comment out all data seeders except user accounts
        // Only seed admin and viewer users, no other data
        $this->call([
            // WitelSeeder::class,
            // AccountManagerSeeder::class,     
            // DummyDataSeeder::class,
        ]);
    }
}
