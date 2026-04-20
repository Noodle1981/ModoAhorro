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
        // Obtener ID de San Juan u otra provincia por defecto
        $sanJuan = \App\Models\Province::where('name', 'San Juan')->first();
        $provinceId = $sanJuan ? $sanJuan->id : 1;

        \App\Models\Proveedor::updateOrCreate(['name' => 'Naturgy'], [
            'name' => 'Naturgy',
            'cuit' => '30-50000000-1',
            'address' => 'Av. Libertador Gral. San Martín 3000, San Juan',
            'phone' => '0810-333-4638',
            'email' => 'clientes@naturgy.com.ar',
            'province_id' => $provinceId,
        ]);

        \App\Models\Proveedor::updateOrCreate(['name' => 'Distribuidora Caucete'], [
            'name' => 'Distribuidora Caucete',
            'cuit' => '30-60000000-2',
            'address' => 'Diag. Sarmiento 500, Caucete, San Juan',
            'phone' => '0264-496-0000',
            'email' => 'contacto@distribuidoracaucete.com.ar',
            'province_id' => $provinceId,
        ]);

        \App\Models\UtilityCompany::updateOrCreate(['name' => 'Naturgy Energía'], [
            'name' => 'Naturgy Energía',
        ]);
    }
}
