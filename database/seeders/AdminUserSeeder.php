<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // CHANGE THESE before running, or edit the created user afterward.
        User::firstOrCreate(
            ['email' => 'admin@zestminds.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Abc123'),
                'role' => 'admin',
            ]
        );
    }
}
