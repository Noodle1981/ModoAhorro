<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClimateData;

class ShowHotDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'climate:hotdays {threshold=28}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra los días con temperatura máxima mayor o igual al umbral (por defecto 28°C)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = $this->argument('threshold');
        $hotDays = ClimateData::where('temp_max', '>=', $threshold)
            ->orderBy('date')
            ->get(['date', 'temp_max']);

        if ($hotDays->isEmpty()) {
            $this->info('No se encontraron días con temp_max >= ' . $threshold . '°C');
            return;
        }

        $this->table(['Fecha', 'Temp. Máxima (°C)'], $hotDays->toArray());
    }
}
