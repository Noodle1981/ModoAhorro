<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowLoadFactors extends Command
{
    protected $signature = 'show:load-factors';
    protected $description = 'Muestra los load_factors de todos los tipos de equipo';

    public function handle()
    {
        $types = DB::table('equipment_types')
            ->select('name', 'load_factor', 'efficiency')
            ->orderBy('name')
            ->get();
        
        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'Tipo de Equipo' => $type->name,
                'Load Factor' => $type->load_factor ?? 'NULL',
                'Efficiency' => $type->efficiency ?? 'NULL',
            ];
        }
        
        $this->table(['Tipo de Equipo', 'Load Factor', 'Efficiency'], $data);
        
        return 0;
    }
}
