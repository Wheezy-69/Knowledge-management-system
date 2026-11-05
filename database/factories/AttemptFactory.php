<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Problem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Attempt> */
class AttemptFactory extends Factory
{
    public function definition(): array
    {
        return ['problem_id' => Problem::factory(), 'code' => '<?php echo "Hello";', 'position' => 1];
    }
}
