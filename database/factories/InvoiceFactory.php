<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'invoice_number' => $this->faker->unique()->numerify('INV-#####'),
            'tariff' => 'T1R2',
            'invoice_date' => now()->format('Y-m-d'),
            'issue_date' => now()->addDays(5)->format('Y-m-d'),
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'total_energy_consumed_kwh' => $this->faker->randomFloat(2, 100, 1000),
            'total_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'installment_number' => 1,
            'total_installments' => 2,
        ];
    }
}
