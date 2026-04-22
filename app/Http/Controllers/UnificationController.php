<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnificationController extends Controller
{
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

        // Get grouping of invoices by contract, start_date and end_date
        // Using string formatting for dates to ensure grouping works in SQLite/SQL
        $unifications = Invoice::whereHas('contract', function($q) use ($entity) {
                $q->where('entity_id', $entity->id);
            })
            ->with(['contract.proveedor'])
            ->get()
            ->groupBy(function($item) {
                return $item->contract_id . '_' . $item->start_date . '_' . $item->end_date;
            })
            ->map(function($group) {
                $first = $group->first();
                $total_kwh = $group->sum('total_energy_consumed_kwh');
                $total_amount = $group->sum('total_amount');
                $installments_count = $group->count();
                
                // Real bimonthly goal (take the highest value found in the group or the first non-null)
                $real_bimonthly_kwh = $group->max('bimonthly_consumption_kwh');
                
                return [
                    'id' => $first->contract_id . '_' . Carbon::parse($first->start_date)->format('Ymd'),
                    'contract_name' => $first->contract->proveedor->name . ' (#' . $first->contract->supply_number . ')',
                    'start_date' => $first->start_date,
                    'end_date' => $first->end_date,
                    'total_kwh' => $total_kwh,
                    'real_bimonthly_kwh' => $real_bimonthly_kwh,
                    'total_amount' => $total_amount,
                    'installments_count' => $installments_count,
                    'status' => $installments_count >= 2 ? 'complete' : 'pending',
                    'invoices' => $group->map(function($inv) {
                        return [
                            'id' => $inv->id,
                            'number' => $inv->invoice_number,
                            'amount' => $inv->total_amount,
                            'kwh' => $inv->total_energy_consumed_kwh,
                            'installment' => $inv->installment_number,
                            'total_installments' => $inv->total_installments,
                        ];
                    })->values()
                ];
            })
            ->values()
            ->sortByDesc('end_date')
            ->values();

        return Inertia::render('Entities/Unifications/Index', [
            'entity' => $entity,
            'unifications' => $unifications,
        ]);
    }
}
