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
        $sanJuan = \App\Models\Province::where('name', 'San Juan')->first();
        $localities = [
            ['name' => 'Capital', 'postal_code' => '5400'],
            ['name' => 'Chimbas', 'postal_code' => '5413'],
            ['name' => 'Rawson', 'postal_code' => '5425'],
            ['name' => 'Pocito', 'postal_code' => '5430'],
        ];
        foreach ($localities as $loc) {
            \App\Models\Locality::create(array_merge($loc, ['province_id' => $sanJuan->id]));
        }
    }
}
