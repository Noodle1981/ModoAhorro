<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $sanJuan = Province::where('name', 'San Juan')->first();
        if ($sanJuan) {
            Company::updateOrCreate([
                'name' => 'Naturgy',
                'province_id' => $sanJuan->id,
            ]);
            Company::updateOrCreate([
                'name' => 'Energia Caucete',
                'province_id' => $sanJuan->id,
            ]);
        }
    }
}
