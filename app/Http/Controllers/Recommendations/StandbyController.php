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
        $result = $service->analyze($entity);
        return view('standby.analysis', compact('entity', 'result'));
    }

    public function toggle(Request $request, string $entityId, string $equipmentId, StandbyAnalysisService $service)
    {
        $service->toggleEquipmentStandby($equipmentId);
        return redirect()->back()->with('success', 'Estado standby actualizado.');
    }
}
