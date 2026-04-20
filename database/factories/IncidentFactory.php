<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recorded_by'      => \App\Models\User::factory(),
            'date_of_incident' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'location'         => fake()->streetAddress(),
            'description'      => fake()->optional(0.7)->paragraph(),
            'status'           => fake()->randomElement(['under_investigation', 'cleared', 'solved']),
        ];
    }
}
