<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\User;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentUsage;
use App\Models\Locality;
use App\Models\UtilityCompany;
use App\Models\Proveedor;

class BackupCasa27Seeder extends Seeder
{
    public function run(): void
    {
        $entity = Entity::updateOrCreate(['name' => 'Casa 27'], array (
  'name' => 'Casa 27',
  'type' => 'hogar',
  'address_street' => 'Calle Carlos Gardel Casa 27 B° Enoe Bravo',
  'address_postal_code' => '5300',
  'locality_id' => 1,
  'description' => 'Casa de prueba',
  'square_meters' => 450.0,
  'people_count' => 4,
  'thermal_profile' => 
  array (
    'roof_type' => 'concrete_slab',
    'window_type' => 'single_glass',
    'window_frame' => 'aluminum',
    'orientation' => 'este_oeste',
    'sun_exposure' => 'medium',
    'roof_insulation' => true,
    'drafts_detected' => false,
    'south_window' => false,
    'thermal_score' => 50,
    'energy_label' => 'D',
  ),
  'opens_at' => NULL,
  'closes_at' => NULL,
  'operating_days' => NULL,
));

        // 1. Find or Create User
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        if (!$user->entities()->where('entity_id', $entity->id)->exists()) {
            $user->entities()->attach($entity->id, ['plan_id' => 1, 'subscribed_at' => now()]);
        }

        $contract = Contract::updateOrCreate(['contract_number' => '36697', 'entity_id' => $entity->id], array (
  'contract_number' => '36697',
  'proveedor_id' => 1,
  'utility_company_id' => 1,
  'serial_number' => NULL,
  'meter_number' => '9618495',
  'client_number' => '07182202700',
  'supply_number' => '07182202700',
  'contract_identifier' => NULL,
  'rate_name' => 'T1-R1',
  'tariff_type' => 'T1-R1',
  'contracted_power_kw_p1' => NULL,
  'contracted_power_kw_p2' => NULL,
  'contracted_power_kw_p3' => NULL,
  'start_date' => '2024-11-22 17:09:10',
  'end_date' => NULL,
  'is_active' => 1,
));

        $invoice = Invoice::updateOrCreate(['invoice_number' => '137756868'], array (
  'invoice_number' => '137756868',
  'issue_date' => '2025-03-28',
  'invoice_date' => NULL,
  'start_date' => '2025-01-15',
  'end_date' => '2025-03-20',
  'energy_consumed_p1_kwh' => NULL,
  'energy_consumed_p2_kwh' => NULL,
  'energy_consumed_p3_kwh' => NULL,
  'total_energy_consumed_kwh' => 624,
  'cost_for_energy' => 67876.86,
  'cost_for_power' => NULL,
  'taxes' => 28078.31,
  'other_charges' => NULL,
  'total_amount' => 95955.17,
  'status' => 'paid',
  'total_energy_injected_kwh' => NULL,
  'surplus_compensation_amount' => NULL,
  'file_path' => NULL,
  'source' => 'manual',
  'co2_footprint_kg' => NULL,
  'is_representative' => true,
  'anomaly_reason' => NULL,
  'usage_locked' => false,
) + ['contract_id' => $contract->id]);

        $invoice = Invoice::updateOrCreate(['invoice_number' => '138579184'], array (
  'invoice_number' => '138579184',
  'issue_date' => '2025-06-25',
  'invoice_date' => NULL,
  'start_date' => '2025-03-21',
  'end_date' => '2025-05-15',
  'energy_consumed_p1_kwh' => NULL,
  'energy_consumed_p2_kwh' => NULL,
  'energy_consumed_p3_kwh' => NULL,
  'total_energy_consumed_kwh' => 123,
  'cost_for_energy' => 13784.62,
  'cost_for_power' => NULL,
  'taxes' => 4743.4,
  'other_charges' => NULL,
  'total_amount' => 18528.02,
  'status' => 'paid',
  'total_energy_injected_kwh' => NULL,
  'surplus_compensation_amount' => NULL,
  'file_path' => NULL,
  'source' => 'manual',
  'co2_footprint_kg' => NULL,
  'is_representative' => true,
  'anomaly_reason' => NULL,
  'usage_locked' => false,
) + ['contract_id' => $contract->id]);

        $invoice = Invoice::updateOrCreate(['invoice_number' => '139151993'], array (
  'invoice_number' => '139151993',
  'issue_date' => '2025-08-27',
  'invoice_date' => NULL,
  'start_date' => '2025-05-14',
  'end_date' => '2025-07-15',
  'energy_consumed_p1_kwh' => NULL,
  'energy_consumed_p2_kwh' => NULL,
  'energy_consumed_p3_kwh' => NULL,
  'total_energy_consumed_kwh' => 83,
  'cost_for_energy' => 8503.49,
  'cost_for_power' => NULL,
  'taxes' => 2452.22,
  'other_charges' => NULL,
  'total_amount' => 10955.71,
  'status' => 'paid',
  'total_energy_injected_kwh' => NULL,
  'surplus_compensation_amount' => NULL,
  'file_path' => NULL,
  'source' => 'manual',
  'co2_footprint_kg' => NULL,
  'is_representative' => true,
  'anomaly_reason' => NULL,
  'usage_locked' => false,
) + ['contract_id' => $contract->id]);

        $invoice = Invoice::updateOrCreate(['invoice_number' => '139459979'], array (
  'invoice_number' => '139459979',
  'issue_date' => '2025-09-26',
  'invoice_date' => NULL,
  'start_date' => '2025-07-16',
  'end_date' => '2025-09-07',
  'energy_consumed_p1_kwh' => NULL,
  'energy_consumed_p2_kwh' => NULL,
  'energy_consumed_p3_kwh' => NULL,
  'total_energy_consumed_kwh' => 78,
  'cost_for_energy' => 8293.53,
  'cost_for_power' => NULL,
  'taxes' => 8293.53,
  'other_charges' => NULL,
  'total_amount' => 10778.25,
  'status' => 'paid',
  'total_energy_injected_kwh' => NULL,
  'surplus_compensation_amount' => NULL,
  'file_path' => NULL,
  'source' => 'manual',
  'co2_footprint_kg' => NULL,
  'is_representative' => true,
  'anomaly_reason' => NULL,
  'usage_locked' => false,
) + ['contract_id' => $contract->id]);

        $room = Room::updateOrCreate(['name' => 'Cocina / Comedor', 'entity_id' => $entity->id], array (
  'name' => 'Cocina / Comedor',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Aire Grande',
  'category_id' => 1,
  'type_id' => 2,
  'nominal_power_w' => 2400,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 8,
  'use_days_in_period' => 45,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Ventilador de Techo',
  'category_id' => 1,
  'type_id' => 6,
  'nominal_power_w' => 60,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Microondas',
  'category_id' => 5,
  'type_id' => 34,
  'nominal_power_w' => 1000,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.2,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.2,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.2,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.2,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Focos Ventilador',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Focos Ventilador',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Focos Ventilador',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Tubo Led Cocina',
  'category_id' => 2,
  'type_id' => 20,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Living', 'entity_id' => $entity->id], array (
  'name' => 'Living',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Ventilador de Techo',
  'category_id' => 1,
  'type_id' => 6,
  'nominal_power_w' => 60,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'TV Grande',
  'category_id' => 4,
  'type_id' => 47,
  'nominal_power_w' => 120,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Foco Living',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Router Wifi',
  'category_id' => 6,
  'type_id' => 58,
  'nominal_power_w' => 20,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Habitación Mamá', 'entity_id' => $entity->id], array (
  'name' => 'Habitación Mamá',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Ventilador de Techo',
  'category_id' => 1,
  'type_id' => 6,
  'nominal_power_w' => 60,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Foco Ventilador',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Foco Mesita de Luz',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Habitación Papa', 'entity_id' => $entity->id], array (
  'name' => 'Habitación Papa',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Ventilador de Techo',
  'category_id' => 1,
  'type_id' => 6,
  'nominal_power_w' => 60,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Foco Ventilador',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 40,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Foco Mesita de Luz',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 40,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'TV Chico',
  'category_id' => 4,
  'type_id' => 46,
  'nominal_power_w' => 85,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 45,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 39,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 43,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 37,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Habitación Hermanos', 'entity_id' => $entity->id], array (
  'name' => 'Habitación Hermanos',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'PC Gamer',
  'category_id' => 6,
  'type_id' => 53,
  'nominal_power_w' => 600,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 38,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 33,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 37,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 32,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Monitor PC',
  'category_id' => 6,
  'type_id' => 54,
  'nominal_power_w' => 50,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 38,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 33,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 37,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 32,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Monitor PC',
  'category_id' => 6,
  'type_id' => 54,
  'nominal_power_w' => 50,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 38,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 33,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 37,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 4,
  'use_days_in_period' => 32,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Ventilador de Techo',
  'category_id' => 1,
  'type_id' => 6,
  'nominal_power_w' => 60,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Foco Ventilador de Techo',
  'category_id' => 2,
  'type_id' => 6,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 5,
  'use_days_in_period' => 51,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 17,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 2,
  'use_days_in_period' => 19,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Mesita de Luz',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Aire Portatil',
  'category_id' => 1,
  'type_id' => 4,
  'nominal_power_w' => 1400,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 8,
  'use_days_in_period' => 45,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Baño', 'entity_id' => $entity->id], array (
  'name' => 'Baño',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Foco Baño',
  'category_id' => 2,
  'type_id' => 17,
  'nominal_power_w' => 12,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Secador de Pelo',
  'category_id' => 8,
  'type_id' => 63,
  'nominal_power_w' => 1000,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.15,
  'use_days_in_period' => 18,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.15,
  'use_days_in_period' => 16,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.15,
  'use_days_in_period' => 18,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.15,
  'use_days_in_period' => 15,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Maquina de Afeitar',
  'category_id' => 8,
  'type_id' => 65,
  'nominal_power_w' => 12,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.05,
  'use_days_in_period' => 32,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.05,
  'use_days_in_period' => 28,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.05,
  'use_days_in_period' => 31,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 0.05,
  'use_days_in_period' => 27,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Fondo', 'entity_id' => $entity->id], array (
  'name' => 'Fondo',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Foco Led Grande',
  'category_id' => 2,
  'type_id' => 17,
  'nominal_power_w' => 12,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Garage', 'entity_id' => $entity->id], array (
  'name' => 'Garage',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Focos Garage',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Focos Garage',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Heladera',
  'category_id' => 3,
  'type_id' => 22,
  'nominal_power_w' => 150,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 24,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Lavarropa',
  'category_id' => 3,
  'type_id' => 25,
  'nominal_power_w' => 2500,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 18,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 16,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 18,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 15,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'semanal',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Hall', 'entity_id' => $entity->id], array (
  'name' => 'Hall',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Foco',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Frente / Vereda', 'entity_id' => $entity->id], array (
  'name' => 'Frente / Vereda',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Foco',
  'category_id' => 2,
  'type_id' => 15,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $room = Room::updateOrCreate(['name' => 'Lavadero', 'entity_id' => $entity->id], array (
  'name' => 'Lavadero',
  'square_meters' => 0,
  'description' => NULL,
));

        $room = Room::updateOrCreate(['name' => 'Portátiles', 'entity_id' => $entity->id], array (
  'name' => 'Portátiles',
  'square_meters' => 0,
  'description' => NULL,
));

        $equipment = Equipment::create(array (
  'name' => 'Cargadores de Celular',
  'category_id' => 7,
  'type_id' => 59,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Cargadores de Celular',
  'category_id' => 7,
  'type_id' => 59,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Cargadores de Celular',
  'category_id' => 7,
  'type_id' => 59,
  'nominal_power_w' => 5,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 64,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 55,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 62,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 1.5,
  'use_days_in_period' => 53,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

        $equipment = Equipment::create(array (
  'name' => 'Notebook',
  'category_id' => 7,
  'type_id' => 52,
  'nominal_power_w' => 65,
  'is_standby' => false,
  'avg_daily_use_hours' => NULL,
  'use_days_per_week' => NULL,
  'is_active' => true,
  'installed_at' => NULL,
  'removed_at' => NULL,
  'acquisition_year' => NULL,
  'energy_label' => NULL,
  'is_inverter' => 0,
  'capacity' => NULL,
  'capacity_unit' => NULL,
) + ['room_id' => $room->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 32,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '137756868')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 28,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '138579184')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 31,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139151993')->first()->id]);

        EquipmentUsage::create(array (
  'is_standby' => 0,
  'avg_daily_use_hours' => 3,
  'use_days_in_period' => 27,
  'use_days_of_week' => NULL,
  'usage_frequency' => 'diario',
  'usage_count' => NULL,
  'avg_use_duration' => NULL,
  'consumption_kwh' => NULL,
) + ['equipment_id' => $equipment->id, 'invoice_id' => Invoice::where('invoice_number', '139459979')->first()->id]);

    }
}
