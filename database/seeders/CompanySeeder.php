<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Company;

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
