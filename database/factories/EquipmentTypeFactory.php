<?php

namespace Database\Factories;

use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentTypeFactory extends Factory
{
    protected $model = EquipmentType::class;

    public function definition()
    {
        return [
            'category_id' => EquipmentCategory::factory(),
            'name' => $this->faker->words(2, true),
            'default_power_watts' => $this->faker->numberBetween(10, 2000),
            'default_avg_daily_use_hours' => $this->faker->randomFloat(1, 0, 24),
            'default_standby_power_w' => $this->faker->randomFloat(1, 0, 10),
            'is_shiftable' => $this->faker->boolean(),
            'is_climatization' => $this->faker->boolean(),
        ];
    }
}
