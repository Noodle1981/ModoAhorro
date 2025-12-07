<?php

namespace App\Http\Controllers;

use App\Models\EfficiencyBenchmark;
use App\Models\EquipmentType;
use Illuminate\Http\Request;

class EfficiencyBenchmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $benchmarks = EfficiencyBenchmark::with('equipmentType.category')->get();
        return view('efficiency_benchmarks.index', compact('benchmarks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = EquipmentType::with('category')->get();
        return view('efficiency_benchmarks.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_type_id' => 'required|exists:equipment_types,id',
            'efficiency_gain_factor' => 'required|numeric|min:0|max:1',
            'average_market_price' => 'required|numeric|min:0',
            'meli_search_term' => 'required|string|max:255',
            'affiliate_link' => 'nullable|url',
        ]);

        EfficiencyBenchmark::create($validated);

        return redirect()->route('efficiency-benchmarks.index')
                         ->with('success', 'Benchmark creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EfficiencyBenchmark $efficiencyBenchmark)
    {
        $types = EquipmentType::with('category')->get();
        return view('efficiency_benchmarks.edit', compact('efficiencyBenchmark', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EfficiencyBenchmark $efficiencyBenchmark)
    {
        $validated = $request->validate([
            'equipment_type_id' => 'required|exists:equipment_types,id',
            'efficiency_gain_factor' => 'required|numeric|min:0|max:1',
            'average_market_price' => 'required|numeric|min:0',
            'meli_search_term' => 'required|string|max:255',
            'affiliate_link' => 'nullable|url',
        ]);

        $efficiencyBenchmark->update($validated);

        return redirect()->route('efficiency-benchmarks.index')
                         ->with('success', 'Benchmark actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EfficiencyBenchmark $efficiencyBenchmark)
    {
        $efficiencyBenchmark->delete();
        return redirect()->route('efficiency-benchmarks.index')
                         ->with('success', 'Benchmark eliminado correctamente.');
    }
}
