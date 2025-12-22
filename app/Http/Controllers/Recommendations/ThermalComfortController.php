<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\Thermal\ThermalScoreService;
use App\Services\Thermal\ThermalAdviceEngine;
use Illuminate\Http\Request;

class ThermalComfortController extends Controller
{
    protected $scoreService;
    protected $adviceEngine;

    public function __construct(ThermalScoreService $scoreService, ThermalAdviceEngine $adviceEngine)
    {
        $this->scoreService = $scoreService;
        $this->adviceEngine = $adviceEngine;
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
        return view('thermal.wizard', compact('entity'));
    }

    public function store(Request $request, Entity $entity)
    {
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

        $result = $this->scoreService->calculate($validated);
        
        $profile = array_merge($validated, [
            'thermal_score' => $result['score'],
            'energy_label' => $result['label']
        ]);

        $entity->update(['thermal_profile' => $profile]);

        return redirect()->route('thermal.result', $entity);
    }

    public function result(Entity $entity)
    {
        if (!$entity->thermal_profile) {
            return redirect()->route('thermal.wizard', $entity);
        }

        $profile = $entity->thermal_profile;
        $scoreResult = $this->scoreService->calculate($profile);
        $recommendations = $this->adviceEngine->generateAdvice($profile, $scoreResult['score']);

        return view('thermal.result', compact('entity', 'profile', 'scoreResult', 'recommendations'));
    }
}
