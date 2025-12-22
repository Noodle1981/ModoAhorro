<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class QuickAccessPanel extends Component
{
    public array $links = [];

    public function mount()
    {
        $this->links = [
            [
                'route' => 'consumption.panel',
                'icon' => 'bi-bar-chart-line',
                'label' => 'Panel de Consumo',
                'color' => 'emerald',
                'description' => 'Analiza tu consumo eléctrico',
            ],
            [
                'route' => 'usage_adjustments.index',
                'icon' => 'bi-sliders',
                'label' => 'Ajustes de Uso',
                'color' => 'blue',
                'description' => 'Calibra tus estimaciones',
            ],
            [
                'route' => 'contracts.index',
                'icon' => 'bi-file-earmark-text',
                'label' => 'Contratos',
                'color' => 'purple',
                'description' => 'Gestiona tus contratos',
            ],
            [
                'route' => 'equipment.index',
                'icon' => 'bi-plug',
                'label' => 'Equipos',
                'color' => 'amber',
                'description' => 'Administra tus equipos',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.quick-access-panel');
    }
}
