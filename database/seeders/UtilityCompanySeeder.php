<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UtilityCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Proveedor::updateOrCreate(['name' => 'Naturgy'], [
            'name' => 'Naturgy',
            'cuit' => '30-12345678-9',
            'address' => 'Av. Libertador 1234, San Juan',
            'phone' => '0800-555-1234',
            'email' => 'info@naturgy.com.ar',
            'province_id' => 1,
        ]);

        \App\Models\UtilityCompany::updateOrCreate(['name' => 'Naturgy Energía'], [
            'name' => 'Naturgy Energía',
        ]);
    }
}
