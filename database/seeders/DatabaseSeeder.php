<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        try {
            $this->call([
                // 1. Datos geográficos y de plataforma
                ProvinceSeeder::class,
                LocalitySeeder::class,
                PlanSeeder::class,
                UtilityCompanySeeder::class,

                // 2. Catálogo maestro (categorías visuales + tipos de equipo + etiquetas)
                MasterCleanCatalogueSeeder::class,

                // 3. Infraestructura de Casa 27 (usuario, entidad, contrato, facturas)
                Casa27Seeder::class,

                // 4. Equipos físicos de Casa 27 por ambiente
                Casa27EquipmentSeeder::class,
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
