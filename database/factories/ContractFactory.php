<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Proveedor;
use App\Models\UtilityCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'proveedor_id' => Proveedor::factory(),
            'utility_company_id' => UtilityCompany::factory(),
            'supply_number' => $this->faker->numerify('##########'),
            'meter_number' => $this->faker->numerify('#######'),
            'client_number' => $this->faker->numerify('##########'),
            'tariff_type' => 'T1-R1',
            'contract_number' => $this->faker->unique()->numerify('#####'),
            'rate_name' => 'T1-R1',
            'is_three_phase' => false,
            'contracted_power_kw_p1' => $this->faker->randomFloat(2, 3, 10),
            'contracted_power_kw_p2' => 0,
            'contracted_power_kw_p3' => 0,
            'start_date' => $this->faker->date(),
            'is_active' => true,
        ];
    }
}
