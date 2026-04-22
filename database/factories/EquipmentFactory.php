<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Room;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition()
    {
        return [
            'room_id' => Room::factory(),
            'category_id' => EquipmentCategory::factory(),
            'type_id' => EquipmentType::factory(),
            'name' => $this->faker->words(3, true),
            'nominal_power_w' => $this->faker->numberBetween(10, 2000),
            'avg_daily_use_hours' => $this->faker->randomFloat(1, 0, 24),
            'is_standby' => $this->faker->boolean(),
            'is_active' => true,
            'is_validated' => true,
        ];
    }
}
