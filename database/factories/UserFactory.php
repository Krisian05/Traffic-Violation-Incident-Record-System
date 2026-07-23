<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email'    => fake()->unique()->safeEmail(),
            'role'     => 'operator',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function provinceAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'province_admin']);
    }

    public function lguAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'operator']);
    }

    public function operator(): static
    {
        return $this->lguAdmin();
    }

    public function treasurer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'treasurer']);
    }

    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'cashier']);
    }

    public function trafficSupervisor(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'traffic_supervisor']);
    }

    public function issuingOfficer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'traffic_officer']);
    }

    public function trafficOfficer(): static
    {
        return $this->issuingOfficer();
    }

    public function recordsOfficer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'records_officer']);
    }

    public function auditor(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'auditor']);
    }
}
