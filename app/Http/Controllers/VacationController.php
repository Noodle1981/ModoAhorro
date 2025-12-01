<?php

namespace App\Http\Controllers;

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
        return view('vacation.index', compact('entity'));
    }

    public function calculate(Request $request, $entityId)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $entity = Entity::with(['rooms.equipment.type'])->findOrFail($entityId);
        $days = $request->input('days');

        $result = $this->vacationService->generateChecklist($entity, $days);

        return view('vacation.result', compact('entity', 'days', 'result'));
    }
}
