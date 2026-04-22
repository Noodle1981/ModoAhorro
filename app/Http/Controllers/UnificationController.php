<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Traits\GroupsInvoices;

class UnificationController extends Controller
{
    use GroupsInvoices;

    /**
     * Display a listing of unified bimonthly periods.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $activeEntityId = session('active_entity_id');
        
        $entity = $user->entities()->where('entities.id', $activeEntityId)->first();
        
        if (!$entity) {
            $entity = $user->entities()->first();
            if ($entity) {
                session(['active_entity_id' => $entity->id]);
            }
        }

        if (!$entity) {
            return redirect()->route('dashboard')->with('error', 'Debes seleccionar una entidad.');
        }

        $unifications = $this->getUnifiedPeriods($entity);

        return Inertia::render('Entities/Unifications/Index', [
            'entity' => $entity,
            'unifications' => $unifications,
        ]);
    }
}
