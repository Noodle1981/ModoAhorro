<?php

namespace App\Http\Controllers;

use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use App\Models\EnergyLabelCoefficient;
use App\Models\EquipmentBenchmark;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        if (!auth()->user() || !auth()->user()->is_super_admin) {
            abort(403, 'No tienes permisos de administrador.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_types' => EquipmentType::count(),
                'total_benchmarks' => EquipmentBenchmark::count(),
                'total_coefficients' => EnergyLabelCoefficient::count(),
            ]
        ]);
    }

    public function equipmentTypes()
    {
        $this->checkAdmin();
        return Inertia::render('Admin/EquipmentTypes', [
            'equipmentTypes' => EquipmentType::with('category')->get(),
            'categories' => EquipmentCategory::all(),
        ]);
    }

    public function efficiencyLabels()
    {
        $this->checkAdmin();
        return Inertia::render('Admin/EfficiencyLabels', [
            'coefficients' => EnergyLabelCoefficient::with('category')->get(),
            'categories' => EquipmentCategory::all(),
        ]);
    }

    public function benchmarks()
    {
        $this->checkAdmin();
        return Inertia::render('Admin/Benchmarks', [
            'benchmarks' => EquipmentBenchmark::with('category')->get(),
            'categories' => EquipmentCategory::all(),
        ]);
    }
}
