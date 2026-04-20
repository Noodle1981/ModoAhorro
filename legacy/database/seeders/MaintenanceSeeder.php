<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;
use App\Models\MaintenanceTask;

class MaintenanceSeeder extends Seeder
{
    public function run()
    {
        // ─────────────────────────────────────────────
        // 1. AIRES ACONDICIONADOS
        // ─────────────────────────────────────────────
        $acTypes = EquipmentType::where('name', 'like', '%Aire Acondicionado%')->get();
        foreach ($acTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpieza de Filtros'],
                ['frequency_days' => 30, 'season_month' => null, 'efficiency_impact' => 0.15]
            );

            if (str_contains(strtolower($type->name), 'portátil') || str_contains(strtolower($type->name), 'portatil')) {
                MaintenanceTask::firstOrCreate(
                    ['equipment_type_id' => $type->id, 'title' => 'Drenaje de Agua'],
                    ['frequency_days' => 30, 'season_month' => null, 'efficiency_impact' => 0.05]
                );
            } else {
                MaintenanceTask::firstOrCreate(
                    ['equipment_type_id' => $type->id, 'title' => 'Limpieza Profunda (Unidad Exterior/Interior)'],
                    ['frequency_days' => 365, 'season_month' => 11, 'efficiency_impact' => 0.20]
                );
                MaintenanceTask::firstOrCreate(
                    ['equipment_type_id' => $type->id, 'title' => 'Verificar Carga de Gas Refrigerante'],
                    ['frequency_days' => 730, 'season_month' => null, 'efficiency_impact' => 0.25]
                );
            }
        }

        // ─────────────────────────────────────────────
        // 2. TERMOTANQUES ELÉCTRICOS
        // ─────────────────────────────────────────────
        $waterHeaterTypes = EquipmentType::where('name', 'like', '%Termotanque%')->get();
        foreach ($waterHeaterTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Revisión de Ánodo de Sacrificio'],
                ['frequency_days' => 365, 'season_month' => null, 'efficiency_impact' => 0.10]
            );
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Purga de Sedimentos (Llave de Drenaje)'],
                ['frequency_days' => 180, 'season_month' => null, 'efficiency_impact' => 0.08]
            );
        }

        // ─────────────────────────────────────────────
        // 3. HELADERAS / FREEZERS
        // ─────────────────────────────────────────────
        $fridgeTypes = EquipmentType::where('name', 'like', '%Heladera%')
            ->orWhere('name', 'like', '%Freezer%')->get();
        foreach ($fridgeTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpieza de Condensador (Rejilla trasera)'],
                ['frequency_days' => 180, 'season_month' => null, 'efficiency_impact' => 0.15]
            );
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Verificar Sellado de Burletes'],
                ['frequency_days' => 365, 'season_month' => null, 'efficiency_impact' => 0.10]
            );
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Descongelar Manualmente (si aplica)'],
                ['frequency_days' => 90, 'season_month' => null, 'efficiency_impact' => 0.12]
            );
        }

        // ─────────────────────────────────────────────
        // 4. LAVARROPAS
        // ─────────────────────────────────────────────
        $washerTypes = EquipmentType::where('name', 'like', '%Lavarropas%')->get();
        foreach ($washerTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpiar Filtro de Pelusa'],
                ['frequency_days' => 30, 'season_month' => null, 'efficiency_impact' => 0.08]
            );
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpieza del Tambor (Ciclo de Limpieza)'],
                ['frequency_days' => 90, 'season_month' => null, 'efficiency_impact' => 0.05]
            );
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Revisar Mangueras y Conexiones'],
                ['frequency_days' => 365, 'season_month' => null, 'efficiency_impact' => 0.03]
            );
        }

        // ─────────────────────────────────────────────
        // 5. MICROONDAS
        // ─────────────────────────────────────────────
        $microTypes = EquipmentType::where('name', 'like', '%Microondas%')->get();
        foreach ($microTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpiar Interior y Plato Giratorio'],
                ['frequency_days' => 30, 'season_month' => null, 'efficiency_impact' => 0.05]
            );
        }

        // ─────────────────────────────────────────────
        // 6. PCs / COMPUTADORAS
        // ─────────────────────────────────────────────
        $pcTypes = EquipmentType::where('name', 'like', '%PC%')
            ->orWhere('name', 'like', '%Gamer%')->get();
        foreach ($pcTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpieza de Polvo Interno (Ventiladores y Disipadores)'],
                ['frequency_days' => 180, 'season_month' => null, 'efficiency_impact' => 0.10]
            );
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Renovar Pasta Térmica del Procesador'],
                ['frequency_days' => 730, 'season_month' => null, 'efficiency_impact' => 0.08]
            );
        }

        // ─────────────────────────────────────────────
        // 7. TVs
        // ─────────────────────────────────────────────
        $tvTypes = EquipmentType::where('name', 'like', '%TV%')->get();
        foreach ($tvTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpiar Rejillas de Ventilación'],
                ['frequency_days' => 180, 'season_month' => null, 'efficiency_impact' => 0.05]
            );
        }

        // ─────────────────────────────────────────────
        // 8. VENTILADORES DE TECHO
        // ─────────────────────────────────────────────
        $fanTypes = EquipmentType::where('name', 'like', '%Ventilador%')->get();
        foreach ($fanTypes as $type) {
            MaintenanceTask::firstOrCreate(
                ['equipment_type_id' => $type->id, 'title' => 'Limpiar Aspas y Verificar Tornillos'],
                ['frequency_days' => 180, 'season_month' => null, 'efficiency_impact' => 0.05]
            );
        }

        $this->command->info('✓ Tareas de mantenimiento cargadas correctamente.');
    }
}
