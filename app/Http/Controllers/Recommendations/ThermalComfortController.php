<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\Thermal\ThermalScoreService;
use App\Services\Thermal\ThermalAdviceEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThermalComfortController extends Controller
{
    protected $profileService;
    protected $adviceEngine;

    public function __construct(\App\Services\ThermalProfileService $profileService, ThermalAdviceEngine $adviceEngine)
    {
        $this->profileService = $profileService;
        $this->adviceEngine = $adviceEngine;
    }

    public function index(Request $request, Entity $entity)
    {
        if ($request->user()->cannot('view', $entity)) {
            abort(403);
        }

        if ($entity->thermal_profile) {
            return redirect()->route('gestion.thermal.result', $entity);
        }
        return redirect()->route('gestion.thermal.wizard', $entity);
    }

    public function wizard(Request $request, Entity $entity)
    {
        if ($request->user()->cannot('view', $entity)) {
            abort(403);
        }

        $config = config("entity_types.{$entity->type}", []);
        return Inertia::render('Thermal/Wizard', [
            'entity' => $entity,
            'config' => $config
        ]);
    }

    public function store(Request $request, Entity $entity)
    {
        if ($request->user()->cannot('view', $entity)) {
            abort(403);
        }

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

        return redirect()->route('gestion.thermal.result', $entity)
            ->with('success', "Diagnóstico completado. Tu casa es Categoría " . $result['energy_label']);
    }

    public function result(Request $request, Entity $entity)
    {
        if ($request->user()->cannot('view', $entity)) {
            abort(403);
        }

        $config = config("entity_types.{$entity->type}", []);
        if (!$entity->thermal_profile) {
            return redirect()->route('gestion.thermal.wizard', $entity);
        }

        $profile = $entity->thermal_profile;
        $scoreResult = $this->profileService->calculate($profile);
        $recommendations = $this->adviceEngine->generateAdvice($profile, $scoreResult['thermal_score']);

        return Inertia::render('Thermal/Result', [
            'entity' => $entity,
            'profile' => $profile,
            'scoreResult' => $scoreResult,
            'recommendations' => $recommendations,
            'config' => $config
        ]);
    }
}
