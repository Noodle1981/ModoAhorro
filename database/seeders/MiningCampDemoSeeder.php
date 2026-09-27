<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\Invoice;
use App\Models\Locality;
use App\Models\Plan;
use App\Models\Proveedor;
use App\Models\Province;
use App\Models\Room;
use App\Models\User;
use App\Models\UtilityCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MiningCampDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Localización Cordillerana Alta Montaña
        $province = Province::firstOrCreate(['name' => 'San Juan']);

        $locality = Locality::updateOrCreate(
            ['name' => 'Campamento Base Veladero'],
            [
                'province_id' => $province->id,
                'latitude' => -29.3500,
                'longitude' => -70.0500,
            ]
        );

        // 2. Usuario Supervisor de Campamento Minero
        $user = User::firstOrCreate(
            ['email' => 'mineria@modoahorro.com'],
            [
                'name' => 'Supervisor de Campamento Veladero',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
            ]
        );

        $plan = Plan::firstOrCreate(
            ['name' => 'Mining Enterprise Pro'],
            [
                'max_entities' => 20,
                'allowed_entity_types' => ['pabellon', 'oficina', 'comercio'],
                'price' => 0,
            ]
        );

        // 3. Fuentes de Suministro (Generador / Mine Grid / Híbrido)
        UtilityCompany::firstOrCreate(
            ['name' => 'Generación Minera Cordillera']
        );

        $fuenteDiesel = Proveedor::firstOrCreate(
            ['name' => 'Generador Caterpillar 500kVA (Grupo Diésel)'],
            ['province_id' => $province->id]
        );

        $fuenteGrid = Proveedor::firstOrCreate(
            ['name' => 'Subestación Mina Veladero (Mine Grid 33kV)'],
            ['province_id' => $province->id]
        );

        $fuenteHibrida = Proveedor::firstOrCreate(
            ['name' => 'Microgrid Híbrida Solar-Diésel'],
            ['province_id' => $province->id]
        );

        // Categorías y Tipos de Equipos
        $catCalef = EquipmentCategory::where('name', 'Calefacción Industrial')->first();
        $catAgua = EquipmentCategory::where('name', 'Agua y Bombeo Industrial')->first();
        $catIlum = EquipmentCategory::where('name', 'Iluminación')->first();
        $catIT = EquipmentCategory::where('name', 'Informática y Oficina')->first();
        $catClima = EquipmentCategory::where('name', 'Climatización y Ambiente')->first();

        $typeConvector = EquipmentType::where('name', 'Convector Eléctrico de Pared')->first();
        $typePanelInfra = EquipmentType::where('name', 'Panel Radiante Infrarrojo')->first();
        $typeTermotanque = EquipmentType::where('name', 'Termotanque Industrial 300L')->first();
        $typeLED = EquipmentType::where('name', 'Luminaria / Lámpara')->first();
        $typePC = EquipmentType::where('name', 'PC de Escritorio')->first();
        $typeServer = EquipmentType::where('name', 'Servidor / Rack IT')->first();
        $typeAire = EquipmentType::where('name', 'Aire Acondicionado Split')->first();

        // ── PABELLÓN A-01: Desvío Crítico (+65%) ──────────────────────────────
        $pabellonA = Entity::firstOrCreate([
            'name' => 'Pabellón A-01 (Dormitorios Turno A)',
        ], [
            'type' => 'pabellon',
            'usage_type' => 'comercial',
            'locality_id' => $locality->id,
            'address_street' => 'Módulo Habitacional Norte - Lote A',
            'camp_shift_type' => '14x14',
            'camp_capacity' => 40,
            'people_count' => 40,
            'module_type' => 'prefab',
            'square_meters' => 280.0,
        ]);
        $user->entities()->syncWithoutDetaching([$pabellonA->id => ['plan_id' => $plan->id, 'subscribed_at' => now()]]);

        $roomDormA = Room::firstOrCreate(['entity_id' => $pabellonA->id, 'name' => 'Dormitorios A']);
        $roomBaniosA = Room::firstOrCreate(['entity_id' => $pabellonA->id, 'name' => 'Baños y Vestuarios A']);

        for ($i = 1; $i <= 20; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomDormA->id,
                'name' => "Convector Eléctrico Hab. A-{$i}",
            ], [
                'type_id' => $typeConvector?->id,
                'category_id' => $catCalef?->id,
                'nominal_power_w' => 1500,
                'has_defined_pattern' => true,
            ]);
        }

        for ($i = 1; $i <= 2; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomBaniosA->id,
                'name' => "Termotanque Industrial 300L N°{$i}",
            ], [
                'type_id' => $typeTermotanque?->id,
                'category_id' => $catAgua?->id,
                'nominal_power_w' => 4500,
                'has_defined_pattern' => true,
            ]);
        }

        for ($i = 1; $i <= 8; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomDormA->id,
                'name' => "Luminaria LED Pasillo A-{$i}",
            ], [
                'type_id' => $typeLED?->id,
                'category_id' => $catIlum?->id,
                'nominal_power_w' => 40,
                'has_defined_pattern' => true,
            ]);
        }

        // Lectura de Tablero simulada (+65% sobre línea base esperada de ~12.500 kWh)
        Invoice::updateOrCreate([
            'invoice_number' => 'TAB-A01-OCT2026',
        ], [
            'entity_id' => $pabellonA->id,
            'issue_date' => now()->subDays(2),
            'start_date' => now()->subDays(30),
            'end_date' => now(),
            'total_energy_consumed_kwh' => 20600.0, // Desvío crítico
            'total_amount' => 7750000.0,
            'source_type' => 'generator',
            'shift_code' => 'TURNO_A_14X14',
            'demand_kw_peak' => 38.5,
            'generator_hours' => 710.0,
            'occupancy_count' => 40,
            'is_representative' => true,
        ]);

        // ── PABELLÓN B-02: Eficiente / Onda Verde (-3%) ───────────────────────
        $pabellonB = Entity::firstOrCreate([
            'name' => 'Pabellón B-02 (Sustentable / Piloto Infrarrojo)',
        ], [
            'type' => 'pabellon',
            'usage_type' => 'comercial',
            'locality_id' => $locality->id,
            'address_street' => 'Módulo Habitacional Sur - Lote B',
            'camp_shift_type' => '14x14',
            'camp_capacity' => 35,
            'people_count' => 35,
            'module_type' => 'container',
            'square_meters' => 240.0,
        ]);
        $user->entities()->syncWithoutDetaching([$pabellonB->id => ['plan_id' => $plan->id, 'subscribed_at' => now()]]);

        $roomDormB = Room::firstOrCreate(['entity_id' => $pabellonB->id, 'name' => 'Dormitorios B']);
        $roomBaniosB = Room::firstOrCreate(['entity_id' => $pabellonB->id, 'name' => 'Baños y Vestuarios B']);

        for ($i = 1; $i <= 18; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomDormB->id,
                'name' => "Panel Radiante Infrarrojo B-{$i}",
            ], [
                'type_id' => $typePanelInfra?->id,
                'category_id' => $catCalef?->id,
                'nominal_power_w' => 900,
                'has_defined_pattern' => true,
            ]);
        }

        Equipment::firstOrCreate([
            'room_id' => $roomBaniosB->id,
            'name' => 'Calefón Solar Tubos de Vacío 300L (Apoyo 1kW)',
        ], [
            'type_id' => $typeTermotanque?->id,
            'category_id' => $catAgua?->id,
            'nominal_power_w' => 1000,
            'has_defined_pattern' => true,
        ]);

        // Lectura de Tablero simulada (-3% sobre línea base: cumplidor Onda Verde)
        Invoice::updateOrCreate([
            'invoice_number' => 'TAB-B02-OCT2026',
        ], [
            'entity_id' => $pabellonB->id,
            'issue_date' => now()->subDays(2),
            'start_date' => now()->subDays(30),
            'end_date' => now(),
            'total_energy_consumed_kwh' => 5600.0, // Cumple y ahorra (-3% bajo línea base)
            'total_amount' => 2100000.0,
            'source_type' => 'solar_hybrid',
            'shift_code' => 'TURNO_B_14X14',
            'demand_kw_peak' => 17.2,
            'generator_hours' => 240.0,
            'occupancy_count' => 35,
            'is_representative' => true,
        ]);

        // ── PABELLÓN C-03: Desvío Reincidente (+107% y 3 períodos) ───────────
        $pabellonC = Entity::firstOrCreate([
            'name' => 'Pabellón C-03 (Contratistas Turno 7x7)',
        ], [
            'type' => 'pabellon',
            'usage_type' => 'comercial',
            'locality_id' => $locality->id,
            'address_street' => 'Módulo Contratistas Este',
            'camp_shift_type' => '7x7',
            'camp_capacity' => 38,
            'people_count' => 38,
            'module_type' => 'prefab',
            'square_meters' => 260.0,
        ]);
        $user->entities()->syncWithoutDetaching([$pabellonC->id => ['plan_id' => $plan->id, 'subscribed_at' => now()]]);

        $roomDormC = Room::firstOrCreate(['entity_id' => $pabellonC->id, 'name' => 'Dormitorios C']);

        for ($i = 1; $i <= 18; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomDormC->id,
                'name' => "Convector Eléctrico C-{$i}",
            ], [
                'type_id' => $typeConvector?->id,
                'category_id' => $catCalef?->id,
                'nominal_power_w' => 2000,
                'has_defined_pattern' => true,
            ]);
        }

        // 3 Períodos consecutivos para activar Acta de Penalización
        for ($i = 2; $i >= 0; $i--) {
            Invoice::updateOrCreate([
                'invoice_number' => 'TAB-C03-P'.(3 - $i),
            ], [
                'entity_id' => $pabellonC->id,
                'issue_date' => now()->subDays($i * 30 + 1),
                'start_date' => now()->subDays(($i + 1) * 30),
                'end_date' => now()->subDays($i * 30),
                'total_energy_consumed_kwh' => 24500.0, // +107% desvío masivo
                'total_amount' => 9200000.0,
                'source_type' => 'generator',
                'shift_code' => 'TURNO_C_7X7',
                'demand_kw_peak' => 45.0,
                'generator_hours' => 720.0,
                'occupancy_count' => 38,
                'is_representative' => true,
            ]);
        }

        // ── OFICINA SUPERVISIÓN: Pabellón Administrativo ──────────────────────
        $oficina = Entity::firstOrCreate([
            'name' => 'Pabellón Administrativo y Supervisión Mina',
        ], [
            'type' => 'oficina',
            'usage_type' => 'comercial',
            'comercio_type' => 'oficina',
            'business_category' => 'oficina',
            'business_subcategory' => 'oficina_corporativa',
            'locality_id' => $locality->id,
            'address_street' => 'Edificio Central de Operaciones',
            'square_meters' => 180.0,
            'people_count' => 8,
            'staff_count' => 8,
        ]);
        $user->entities()->syncWithoutDetaching([$oficina->id => ['plan_id' => $plan->id, 'subscribed_at' => now()]]);

        $roomOffice = Room::firstOrCreate(['entity_id' => $oficina->id, 'name' => 'Sala de Ingeniería y Racks']);

        for ($i = 1; $i <= 6; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomOffice->id,
                'name' => "Estación de Trabajo {$i}",
            ], [
                'type_id' => $typePC?->id,
                'category_id' => $catIT?->id,
                'nominal_power_w' => 250,
                'has_defined_pattern' => true,
            ]);
        }

        for ($i = 1; $i <= 2; $i++) {
            Equipment::firstOrCreate([
                'room_id' => $roomOffice->id,
                'name' => "Servidor Rack IT {$i}",
            ], [
                'type_id' => $typeServer?->id,
                'category_id' => $catIT?->id,
                'nominal_power_w' => 400,
                'has_defined_pattern' => true,
            ]);
        }

        Equipment::firstOrCreate([
            'room_id' => $roomOffice->id,
            'name' => 'Sistema VRF Climatización Oficina',
        ], [
            'type_id' => $typeAire?->id,
            'category_id' => $catClima?->id,
            'nominal_power_w' => 4500,
            'has_defined_pattern' => true,
        ]);

        Invoice::updateOrCreate([
            'invoice_number' => 'TAB-ADM-OCT2026',
        ], [
            'entity_id' => $oficina->id,
            'issue_date' => now()->subDays(2),
            'start_date' => now()->subDays(30),
            'end_date' => now(),
            'total_energy_consumed_kwh' => 4200.0,
            'total_amount' => 1550000.0,
            'source_type' => 'mine_grid',
            'shift_code' => 'TURNO_ADMIN_DIURNO',
            'demand_kw_peak' => 8.5,
            'generator_hours' => 0.0,
            'occupancy_count' => 8,
            'is_representative' => true,
        ]);
    }
}
