<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use Illuminate\Http\Request;

class ThermalWizardController extends Controller
{
    /**
     * Muestra el formulario del asistente térmico.
     */
    public function show(Entity $entity)
    {
        return view('entities.thermal_wizard', compact('entity'));
    }

    /**
     * Procesa los datos del asistente y actualiza la entidad.
     */
    public function update(Request $request, Entity $entity)
    {
        $validated = $request->validate([
            'roof_type' => 'required|string',
            'roof_insulation' => 'required|boolean',
            'window_type' => 'required|string',
            'window_frame' => 'required|string',
            'orientation' => 'required|string',
            'sun_exposure' => 'required|string',
            'drafts_detected' => 'required|boolean',
            'south_window' => 'required|boolean',
        ]);

        // Guardar en el array thermal_profile
        $entity->thermal_profile = array_merge($entity->thermal_profile ?? [], $validated);
        
        // El método updateThermalLabel() en el modelo se encarga de calcular el Label
        $entity->updateThermalLabel();

        return redirect()->route('home.show', $entity->id)
            ->with('success', "Perfil térmico actualizado. Categoría obtenida: " . $entity->thermal_profile['energy_label']);
    }
}
