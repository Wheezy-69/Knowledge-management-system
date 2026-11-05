<?php

namespace Database\Factories;

use App\Models\Vault;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vault> */
class VaultFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->words(3, true), 'description' => fake()->sentence()];
    }
}
