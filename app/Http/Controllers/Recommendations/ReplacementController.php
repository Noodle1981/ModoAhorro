<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Invoice;
use App\Services\Recommendations\ReplacementService;

class ReplacementController extends Controller
{
    protected $replacementService;

    public function __construct(ReplacementService $replacementService)
    {
        $this->replacementService = $replacementService;
    }

    public function index(Entity $entity)
    {
        $config = config("entity_types.{$entity->type}", []);
        
        $entity->load(['rooms.equipment.type.category', 'rooms.equipment.category']);

        $invoice = Invoice::whereHas('contract', function ($query) use ($entity) {
            $query->where('entity_id', $entity->id);
        })->latest('end_date')->first();

        $opportunities = $this->replacementService->generateOpportunities($entity, $invoice);

        // All equipment for the refinement table
        $analyzableEquipments = $entity->rooms
            ->flatMap->equipment
            ->filter(fn($eq) => $eq->type !== null)
            ->unique('id')
            ->values();

        return view('replacements.index', compact('entity', 'opportunities', 'invoice', 'analyzableEquipments', 'config'));
    }

    public function refine($id)
    {
        $equipment = \App\Models\Equipment::findOrFail($id);
        $entity = $equipment->room->entity;
        $config = config("entity_types.{$entity->type}", []);
        return view('replacements.refine', compact('equipment', 'config'));
    }

    public function updateRefinement(Request $request, $id)
    {
        $equipment = \App\Models\Equipment::findOrFail($id);
        $entity = $equipment->room->entity;
        $config = config("entity_types.{$entity->type}", []);
        
        $validated = $request->validate([
            'acquisition_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'energy_label' => 'nullable|string|in:A+++,A++,A+,A,B,C,D,E',
            'capacity' => 'nullable|numeric|min:0',
            'capacity_unit' => 'nullable|string|in:frigorias,litros,kg,btu,watts',
            'is_inverter' => 'nullable|boolean',
        ]);

        $validated['is_inverter'] = $request->has('is_inverter');

        $equipment->update($validated);

        return redirect()->route($config['route_prefix'] . '.replacements', $entity->id)->with('success', 'Datos actualizados. La recomendación ha sido recalculada.');
    }
}
