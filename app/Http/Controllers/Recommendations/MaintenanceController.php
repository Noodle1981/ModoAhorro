<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Equipment;
use App\Services\MaintenanceService;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceTask;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    protected $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    public function index($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        
        $equipments = Equipment::whereHas('room', function($q) use ($entityId) {
            $q->where('entity_id', $entityId);
        })->with(['type.maintenanceTasks', 'room'])->get();

        $maintenanceData = [];
        
        foreach ($equipments as $equipment) {
            if ($equipment->type && $equipment->type->maintenanceTasks->isNotEmpty()) {
                $status = $this->maintenanceService->checkStatus($equipment);
                $maintenanceData[] = [
                    'equipment' => $equipment,
                    'status' => $status
                ];
            }
        }

        return view('maintenance.index', compact('entity', 'maintenanceData', 'config'));
    }

    public function storeLog(Request $request, $equipmentId)
    {
        $request->validate([
            'maintenance_task_id' => 'required|exists:maintenance_tasks,id',
            'notes' => 'nullable|string'
        ]);

        MaintenanceLog::create([
            'equipment_id' => $equipmentId,
            'maintenance_task_id' => $request->maintenance_task_id,
            'completed_at' => Carbon::now(),
            'notes' => $request->notes
        ]);

        return redirect()->back()->with('success', 'Mantenimiento registrado correctamente.');
    }
}
