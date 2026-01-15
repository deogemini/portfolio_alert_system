<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@eportsolutions.co.tz'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
            ]
        );
    }
}
