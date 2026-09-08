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
        // The protected superadmin runs first, so there is always a way in
        // even if the seeders below fail.
        $this->call(SuperAdminSeeder::class);

        if (app()->environment('local', 'testing')) {
            User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => bcrypt('password'),
                    'role' => User::ROLE_ADMIN,
                    'two_factor_type' => User::TWO_FACTOR_TYPE_EMAIL,
                    'email_verified_at' => now(),
                ],
            );
        }

        $this->call(WebsiteContentSeeder::class);
    }
}
