<?php

namespace App\Http\Controllers;

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
        $invoice = Invoice::whereHas('contract', function ($query) use ($entity) {
            $query->where('entity_id', $entity->id);
        })->latest('end_date')->first();

        $service = new \App\Services\Recommendations\ReplacementService();
        $opportunities = $invoice ? $service->generateOpportunities($invoice) : [];

        $analyzableEquipments = [];
        if ($invoice) {
             $analyzableEquipments = $invoice->equipmentUsages()->with('equipment')->get()->pluck('equipment')->unique('id');
        }

        return view('replacements.index', compact('entity', 'opportunities', 'invoice', 'analyzableEquipments'));
    }

    public function refine($id)
    {
        $equipment = \App\Models\Equipment::findOrFail($id);
        return view('replacements.refine', compact('equipment'));
    }

    public function updateRefinement(Request $request, $id)
    {
        $equipment = \App\Models\Equipment::findOrFail($id);
        
        $validated = $request->validate([
            'acquisition_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'energy_label' => 'nullable|string|in:A+++,A++,A+,A,B,C,D,E',
            'capacity' => 'nullable|numeric|min:0',
            'capacity_unit' => 'nullable|string|in:frigorias,litros,kg,btu,watts',
            'is_inverter' => 'nullable|boolean',
        ]);

        $validated['is_inverter'] = $request->has('is_inverter');

        $equipment->update($validated);

        // Redirigir de vuelta a la lista de reemplazos de la entidad del equipo
        // Necesitamos encontrar la entidad a través de la sala
        $entityId = $equipment->room->entity_id;

        return redirect()->route('replacements.index', $entityId)->with('success', 'Datos actualizados. La recomendación ha sido recalculada.');
    }
}
