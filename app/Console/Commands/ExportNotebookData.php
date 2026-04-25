<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('app:export-notebook')]
#[Description('Exporta tablas de la DB a CSV para análisis en NotebookLM')]
class ExportNotebookData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = [
            'entities', 'rooms', 'equipment', 'equipment_categories', 
            'equipment_types', 'contracts', 'invoices', 'equipment_usages', 
            'utility_companies'
        ];

        $outputDir = base_path('tablas');
        if (!file_exists($outputDir)) mkdir($outputDir, 0755, true);

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;

            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                $this->warn("Tabla vacía: $table");
                continue;
            }

            $filename = $outputDir . '/' . $table . '.csv';
            $fp = fopen($filename, 'w');

            $headers = array_keys((array)$rows[0]);
            fputcsv($fp, $headers);

            foreach ($rows as $row) {
                fputcsv($fp, (array)$row);
            }

            fclose($fp);
            $this->info("Exportada: $table");
        }
        
        $this->info("Sincronización de datos completada en /tablas");
    }
}
