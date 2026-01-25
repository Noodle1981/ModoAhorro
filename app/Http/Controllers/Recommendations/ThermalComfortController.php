<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\Thermal\ThermalScoreService;
use App\Services\Thermal\ThermalAdviceEngine;
use Illuminate\Http\Request;

class ThermalComfortController extends Controller
{
    protected $profileService;

    public function __construct(\App\Services\ThermalProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index(Entity $entity)
    {
        if ($entity->thermal_profile) {
            return redirect()->route('thermal.result', $entity);
        }
        return redirect()->route('thermal.wizard', $entity);
    }

    public function wizard(Entity $entity)
    {
        $config = config("entity_types.{$entity->type}", []);
        return view('thermal.wizard', compact('entity', 'config'));
    }

    public function store(Request $request, Entity $entity)
    {
        $config = config("entity_types.{$entity->type}", []);
        $validated = $request->validate([
            'roof_type' => 'required|string',
            'window_type' => 'required|string',
            'window_frame' => 'required|string',
            'orientation' => 'required|string|in:norte_sur,este_oeste,diagonal',
            'sun_exposure' => 'required|string',
        ]);

        $validated['roof_insulation'] = $request->has('roof_insulation');
        $validated['drafts_detected'] = $request->has('drafts_detected');
        $validated['south_window'] = $request->has('south_window');

        $result = $this->profileService->calculate($validated);
        
        $profile = array_merge($validated, [
            'thermal_score' => $result['thermal_score'],
            'energy_label' => $result['energy_label']
        ]);

        $entity->update(['thermal_profile' => $profile]);

        return redirect()->route($config['route_prefix'] . '.show', $entity)
            ->with('success', "Diagnóstico completado. Tu casa es Categoría " . $result['energy_label']);
    }

    public function result(Entity $entity)
    {
        $config = config("entity_types.{$entity->type}", []);
        if (!$entity->thermal_profile) {
            return redirect()->route($config['route_prefix'] . '.thermal.wizard', $entity);
        }

        $profile = $entity->thermal_profile;
        $scoreResult = $this->scoreService->calculate($profile);
        $recommendations = $this->adviceEngine->generateAdvice($profile, $scoreResult['score']);

        return view('thermal.result', compact('entity', 'profile', 'scoreResult', 'recommendations', 'config'));
    }
}
