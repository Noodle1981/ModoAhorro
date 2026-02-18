<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\StandbyAnalysisService;
use Illuminate\Http\Request;

class StandbyController extends Controller
{
    public function analysis(string $id, StandbyAnalysisService $service)
    {
        $entity = Entity::findOrFail($id);
        $config = config("entity_types.{$entity->type}", []);
        $result = $service->calculateStandbyAnalysis($entity);
        // Unpack result array so the view can use variables directly
        extract($result); // equipmentList, totalStandbyKwh, totalStandbyCost, totalPotentialSavings, totalRealizedSavings
        return view('entities.standby_analysis', compact('entity', 'config', 'equipmentList', 'totalStandbyKwh', 'totalStandbyCost', 'totalPotentialSavings', 'totalRealizedSavings'));
    }

    public function toggle(Request $request, string $entityId, string $equipmentId, StandbyAnalysisService $service)
    {
        $service->toggleEquipmentStandby($equipmentId);
        return redirect()->back()->with('success', 'Estado standby actualizado.');
    }
}
