<?php

namespace Database\Factories;

use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
class EntityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Entidad Test',
            'type' => 'hogar',
            'address_street' => 'Calle Test 123',
            'address_postal_code' => '5400',
            'locality_id' => 1, // Asumimos que existe una localidad
            'square_meters' => 100,
            'people_count' => 4,
        ];
    }
}
