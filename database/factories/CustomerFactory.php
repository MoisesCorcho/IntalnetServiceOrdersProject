<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->optional(0.7)->safeEmail(),
            'phone' => fake()->optional(0.8)->e164PhoneNumber(),
            'secondary_phone' => fake()->optional(0.4)->e164PhoneNumber(),
        ];
    }
}
