<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
// use Database\Seeders\CustomerSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample staff account for quick login
        User::query()->updateOrCreate(
            ['email' => 'staff@mawasa.com'],
            [
                'name' => 'Sample Staff',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        // Ensure an admin exists
        User::query()->updateOrCreate(
            ['email' => 'admin@mawasa.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Intentionally not seeding customers by default; enable if required.
        // $this->call(CustomerSeeder::class);

        $this->call(BillingRecordSeeder::class);
    }
}
