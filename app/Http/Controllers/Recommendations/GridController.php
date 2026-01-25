<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\GridOptimizerService;

class GridController extends Controller
{
    public function optimization(string $id, GridOptimizerService $optimizer)
    {
        $entity = Entity::findOrFail($id);
        $config = config("entity_types.{$entity->type}", []);
        
        $equipment = $entity->rooms->flatMap(fn($r) => $r->equipment);
        $schedule = $optimizer->generateSchedule($entity);

        $totalSavings = collect($schedule)->flatMap(fn($s) => $s['equipment'])
            ->sum(fn($e) => $e['savings_potential'] ?? 0);

        return view('grid.optimization', compact('entity', 'schedule', 'totalSavings', 'config'));
    }
}
