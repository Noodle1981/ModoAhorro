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
            ['name' => 'Santa Lucía', 'postal_code' => '5411', 'latitude' => -31.5333, 'longitude' => -68.4833],
            ['name' => 'Rivadavia', 'postal_code' => '5401', 'latitude' => -31.5333, 'longitude' => -68.5833],
            ['name' => 'Rawson', 'postal_code' => '5425', 'latitude' => -31.5754, 'longitude' => -68.5631],
            ['name' => 'Pocito', 'postal_code' => '5430', 'latitude' => -31.6894, 'longitude' => -68.5789],
            ['name' => 'Albardón', 'postal_code' => '5419', 'latitude' => -31.4333, 'longitude' => -68.5333],
            ['name' => 'Angaco', 'postal_code' => '5417', 'latitude' => -31.4244, 'longitude' => -68.4111],
            ['name' => 'Calingasta', 'postal_code' => '5405', 'latitude' => -31.3323, 'longitude' => -69.4181],
            ['name' => 'Caucete', 'postal_code' => '5440', 'latitude' => -31.6508, 'longitude' => -68.2828],
            ['name' => 'Iglesia', 'postal_code' => '5465', 'latitude' => -30.3411, 'longitude' => -69.1953],
            ['name' => 'Jáchal', 'postal_code' => '5460', 'latitude' => -30.2403, 'longitude' => -68.7469],
            ['name' => '9 de Julio', 'postal_code' => '5415', 'latitude' => -31.6500, 'longitude' => -68.3833],
            ['name' => 'San Martín', 'postal_code' => '5439', 'latitude' => -31.5000, 'longitude' => -68.2833],
            ['name' => 'Sarmiento', 'postal_code' => '5435', 'latitude' => -31.9547, 'longitude' => -68.4619],
            ['name' => 'Ullum', 'postal_code' => '5409', 'latitude' => -31.4117, 'longitude' => -68.7297],
            ['name' => 'Valle Fértil', 'postal_code' => '5449', 'latitude' => -30.6381, 'longitude' => -67.5456],
            ['name' => '25 de Mayo', 'postal_code' => '5445', 'latitude' => -31.8122, 'longitude' => -68.2161],
            ['name' => 'Zonda', 'postal_code' => '5403', 'latitude' => -31.5500, 'longitude' => -68.7500],
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
