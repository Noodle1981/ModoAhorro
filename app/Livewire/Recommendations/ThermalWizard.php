<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Models\Entity;
use App\Services\ThermalProfileService;

#[Layout('layouts.app')]
class ThermalWizard extends Component
{
    public Entity $entity;
    
    // Form fields
    public $roof_type = '';
    public $roof_insulation = false;
    public $window_type = '';
    public $window_frame = '';
    public $drafts_detected = false;
    public $orientation = '';
    public $south_window = false;
    public $sun_exposure = '';

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
        
        // Cargar datos existentes si los hay
        if ($this->entity->thermal_profile) {
            $profile = $this->entity->thermal_profile;
            $this->roof_type = $profile['roof_type'] ?? '';
            $this->roof_insulation = $profile['roof_insulation'] ?? false;
            $this->window_type = $profile['window_type'] ?? '';
            $this->window_frame = $profile['window_frame'] ?? '';
            $this->drafts_detected = $profile['drafts_detected'] ?? false;
            $this->orientation = $profile['orientation'] ?? '';
            $this->south_window = $profile['south_window'] ?? false;
            $this->sun_exposure = $profile['sun_exposure'] ?? '';
        }
    }

    public function rules()
    {
        return [
            'roof_type' => 'required|string',
            'window_type' => 'required|string',
            'window_frame' => 'required|string',
            'orientation' => 'required|string|in:norte_sur,este_oeste,diagonal',
            'sun_exposure' => 'required|string',
        ];
    }

    public function save(ThermalProfileService $profileService)
    {
        $validated = $this->validate();
        
        // Agregar campos booleanos que no están estrictamente en las reglas de 'required'
        $validated['roof_insulation'] = $this->roof_insulation;
        $validated['drafts_detected'] = $this->drafts_detected;
        $validated['south_window'] = $this->south_window;

        $result = $profileService->calculate($validated);
        
        $profile = array_merge($validated, [
            'thermal_score' => $result['thermal_score'],
            'energy_label' => $result['energy_label']
        ]);

        $this->entity->update(['thermal_profile' => $profile]);

        $config = config("entity_types.{$this->entity->type}", []);
        
        session()->flash('success', "Diagnóstico completado. Tu casa es Categoría " . $result['energy_label']);
        
        return redirect()->route($config['route_prefix'] . '.show', $this->entity);
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.thermal-wizard', [
            'config' => $config
        ]);
    }

    #[Computed]
    public function progress()
    {
        $steps = ['roof_type', 'window_type', 'orientation', 'sun_exposure'];
        $filled = 0;
        foreach ($steps as $step) {
            if ($this->$step) $filled++;
        }
        return ($filled / count($steps)) * 100;
    }
}
