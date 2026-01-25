<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entity;
use App\Services\VacationService;

class VacationController extends Controller
{
    protected $vacationService;

    public function __construct(VacationService $vacationService)
    {
        $this->vacationService = $vacationService;
    }

    public function index($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        return view('vacation.index', compact('entity', 'config'));
    }

    public function calculate(Request $request, $entityId)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $entity = Entity::with(['rooms.equipment.type'])->findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $days = $request->input('days');

        $result = $this->vacationService->generateChecklist($entity, $days);

        return view('vacation.result', compact('entity', 'days', 'result', 'config'));
    }

    public function confirm(Request $request, $entityId)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $days = $request->input('days');

        $markedCount = $this->vacationService->markAnomalousInvoices($entity, $days);

        $message = '¡Buen viaje! Tu plan de ahorro ha sido registrado.';
        if ($markedCount > 0) {
            $message .= " Se han marcado $markedCount facturas como 'Modo Vacaciones' para no afectar tus estadísticas.";
        }

        return redirect()->route($config['route_prefix'] . '.show', $entity->id)->with('success', $message);
    }
}
