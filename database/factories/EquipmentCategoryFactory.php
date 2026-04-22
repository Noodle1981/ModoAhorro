<?php

namespace Database\Factories;

use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentCategoryFactory extends Factory
{
    protected $model = EquipmentCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
        ];
    }
}
