<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'last_name' => 'User',
            'phone' => '1234567890',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
    }
}

