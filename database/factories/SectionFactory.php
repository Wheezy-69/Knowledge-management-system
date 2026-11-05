<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Section> */
class SectionFactory extends Factory
{
    public function definition(): array
    {
        return ['vault_id' => Vault::factory(), 'name' => fake()->words(3, true), 'description' => null];
    }
}
