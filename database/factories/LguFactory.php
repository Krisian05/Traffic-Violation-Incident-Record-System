<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lgu>
 */
class LguFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'     => strtoupper(fake()->unique()->lexify('???')),
            'name'     => fake()->unique()->city(),
            'province' => 'Cebu',
        ];
    }
}
