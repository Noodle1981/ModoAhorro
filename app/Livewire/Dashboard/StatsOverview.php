<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Equipment;

class StatsOverview extends Component
{
    public function getStatsProperty()
    {
        $userId = auth()->id();
        
        $entitiesCount = Entity::whereHas('users', fn($q) => $q->where('user_id', $userId))->count();
        
        $invoices = Invoice::whereHas('contract.entity.users', fn($q) => $q->where('user_id', $userId));
        $totalConsumption = $invoices->sum('total_energy_consumed_kwh');
        $totalCost = $invoices->sum('total_amount');
        
        $equipmentCount = Equipment::whereHas('room.entity.users', fn($q) => $q->where('user_id', $userId))->count();
        
        return [
            'entities' => $entitiesCount,
            'consumption' => number_format($totalConsumption, 0, ',', '.'),
            'cost' => number_format($totalCost, 0, ',', '.'),
            'equipment' => $equipmentCount,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.stats-overview', [
            'stats' => $this->stats,
        ]);
    }
}
