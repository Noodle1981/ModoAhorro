<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditDashboardController extends Controller
{
    public function index()
    {
        // Obtener facturas que tienen al menos un equipo con audit_logs
        $invoices = \App\Models\Invoice::whereHas('equipmentUsages', function ($query) {
            $query->whereNotNull('audit_logs');
        })
        ->with(['contract.entity', 'equipmentUsages.equipment'])
        ->orderByDesc('created_at')
        ->paginate(10);

        return view('admin.audit.dashboard', compact('invoices'));
    }
}
