<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Proveedor;
use Carbon\Carbon;

class SeederTestHogar extends Seeder
{
    public function run()
    {
        // Crear entidad
        $entity = Entity::create([
            'name' => 'Casa 27',
            'type' => 'Hogar',
            'address_street' => 'Carlos Gardel Casa 27 B° Enoe Bravo',
            'address_postal_code' => '',
            'locality_id' => 1, // Asume que Capital tiene id=1
            'description' => 'Casa de prueba',
            'square_meters' => 450,
            'people_count' => 4,
        ]);

        // Crear proveedor si no existe
        $proveedor = Proveedor::firstOrCreate(['name' => 'Naturgy']);

        // Crear contrato
        $contract = Contract::create([
            'entity_id' => $entity->id,
            'supply_number' => '07182202700',
            'serial_number' => '9618495',
            'proveedor_id' => $proveedor->id,
            'contract_identifier' => '36697',
            'rate_name' => 'T1-R1',
            'contracted_power_kw_p1' => null,
            'contracted_power_kw_p2' => null,
            'contracted_power_kw_p3' => null,
            'start_date' => Carbon::createFromFormat('d/m/Y', '15/01/2025'),
            'end_date' => null,
            'is_active' => true,
        ]);

        // Facturas
        $facturas = [
            [
                'invoice_number' => '137756868',
                'invoice_date' => Carbon::createFromFormat('d/m/Y', '28/03/2025'),
                'start_date' => Carbon::createFromFormat('d/m/Y', '15/01/2025'),
                'end_date' => Carbon::createFromFormat('d/m/Y', '20/03/2025'),
                'total_energy_consumed_kwh' => 624.00,
                'cost_for_energy' => 67876.86,
                'taxes' => 28078.31,
                'total_amount' => 95955.17,
            ],
            [
                'invoice_number' => '138579184',
                'invoice_date' => Carbon::createFromFormat('d/m/Y', '25/06/2025'),
                'start_date' => Carbon::createFromFormat('d/m/Y', '21/03/2025'),
                'end_date' => Carbon::createFromFormat('d/m/Y', '15/05/2025'),
                'total_energy_consumed_kwh' => 123.00,
                'cost_for_energy' => 13784.62,
                'taxes' => 4743.40,
                'total_amount' => 18528.02,
            ],
            [
                'invoice_number' => '139151993',
                'invoice_date' => Carbon::createFromFormat('d/m/Y', '27/08/2025'),
                'start_date' => Carbon::createFromFormat('d/m/Y', '14/05/2025'),
                'end_date' => Carbon::createFromFormat('d/m/Y', '15/07/2025'),
                'total_energy_consumed_kwh' => 83.00,
                'cost_for_energy' => 8503.49,
                'taxes' => 2452.22,
                'total_amount' => 10955.71,
            ],
            [
                'invoice_number' => '139459979',
                'invoice_date' => Carbon::createFromFormat('d/m/Y', '26/09/2025'),
                'start_date' => Carbon::createFromFormat('d/m/Y', '16/07/2025'),
                'end_date' => Carbon::createFromFormat('d/m/Y', '07/09/2025'),
                'total_energy_consumed_kwh' => 78.00,
                'cost_for_energy' => 8293.53,
                'taxes' => 8293.53,
                'total_amount' => 10778.25,
            ],
        ];

        foreach ($facturas as $factura) {
            Invoice::create(array_merge($factura, [
                'contract_id' => $contract->id,
            ]));
        }
    }
}
