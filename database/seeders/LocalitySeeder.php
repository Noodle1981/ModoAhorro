<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // San Juan (Provincia del usuario)
        $sanJuan = \App\Models\Province::where('name', 'San Juan')->first();
        $localities = [
            ['name' => 'Capital', 'postal_code' => '5400', 'latitude' => -31.5375, 'longitude' => -68.5364],
            ['name' => 'Chimbas', 'postal_code' => '5413', 'latitude' => -31.4664, 'longitude' => -68.5347],
            ['name' => 'Rawson', 'postal_code' => '5425', 'latitude' => -31.5754, 'longitude' => -68.5631],
            ['name' => 'Pocito', 'postal_code' => '5430', 'latitude' => -31.6894, 'longitude' => -68.5789],
        ];
        foreach ($localities as $loc) {
            \App\Models\Locality::updateOrCreate(
                ['name' => $loc['name'], 'province_id' => $sanJuan->id],
                $loc
            );
        }
        
        // Otras capitales provinciales principales (para futuro escalado)
        $provinces = [
            ['province' => 'Buenos Aires', 'localities' => [
                ['name' => 'La Plata', 'postal_code' => '1900', 'latitude' => -34.9215, 'longitude' => -57.9545],
            ]],
            ['province' => 'Córdoba', 'localities' => [
                ['name' => 'Córdoba', 'postal_code' => '5000', 'latitude' => -31.4201, 'longitude' => -64.1888],
            ]],
            ['province' => 'Santa Fe', 'localities' => [
                ['name' => 'Santa Fe', 'postal_code' => '3000', 'latitude' => -31.6107, 'longitude' => -60.6973],
                ['name' => 'Rosario', 'postal_code' => '2000', 'latitude' => -32.9468, 'longitude' => -60.6393],
            ]],
            ['province' => 'Mendoza', 'localities' => [
                ['name' => 'Mendoza', 'postal_code' => '5500', 'latitude' => -32.8895, 'longitude' => -68.8458],
            ]],
            ['province' => 'Tucumán', 'localities' => [
                ['name' => 'San Miguel de Tucumán', 'postal_code' => '4000', 'latitude' => -26.8083, 'longitude' => -65.2176],
            ]],
            ['province' => 'Salta', 'localities' => [
                ['name' => 'Salta', 'postal_code' => '4400', 'latitude' => -24.7859, 'longitude' => -65.4117],
            ]],
            ['province' => 'Ciudad Autónoma de Buenos Aires', 'localities' => [
                ['name' => 'CABA', 'postal_code' => '1000', 'latitude' => -34.6037, 'longitude' => -58.3816],
            ]],
        ];
        
        foreach ($provinces as $provData) {
            $province = \App\Models\Province::where('name', $provData['province'])->first();
            if ($province) {
                foreach ($provData['localities'] as $loc) {
                    \App\Models\Locality::updateOrCreate(
                        ['name' => $loc['name'], 'province_id' => $province->id],
                        $loc
                    );
                }
            }
        }
    }
}
