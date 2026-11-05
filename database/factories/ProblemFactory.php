<?php

namespace Database\Factories;

use App\Models\Problem;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Problem> */
class ProblemFactory extends Factory
{
    public function definition(): array
    {
        return ['section_id' => Section::factory(), 'title' => fake()->sentence(), 'programming_language' => 'php', 'tags' => []];
    }
}
