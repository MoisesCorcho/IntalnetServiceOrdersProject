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
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'last_name' => 'User',
                'phone' => '1234567890',
                'password' => Hash::make('password'),
            ]
        );

        $techniciansData = [
            ['name' => 'Laura', 'last_name' => 'Gonzalez', 'email' => 'laura.gonzalez@example.com'],
            ['name' => 'Carlos', 'last_name' => 'Diaz', 'email' => 'carlos.diaz@example.com'],
            ['name' => 'Monica', 'last_name' => 'Perez', 'email' => 'monica.perez@example.com'],
        ];

        foreach ($techniciansData as $technicianData) {
            $technician = User::firstOrCreate(
                ['email' => $technicianData['email']],
                $technicianData + [
                    'phone' => fake()->e164PhoneNumber(),
                    'password' => Hash::make('password'),
                ]
            );

            $technician->assignRole('tecnico');

            if (!$technician->addresses()->exists()) {
                $technician->addresses()->create([
                    'street' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'zip' => fake()->postcode(),
                ]);
            }
        }
    }
}

