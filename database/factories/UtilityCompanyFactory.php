<?php

namespace Database\Factories;

use App\Models\UtilityCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UtilityCompany>
 */
class UtilityCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company().' Group '.$this->faker->unique()->randomNumber(5),
        ];
    }
}
