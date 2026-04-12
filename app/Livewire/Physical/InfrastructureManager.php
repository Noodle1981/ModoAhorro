<?php

namespace App\Livewire\Physical;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Entity;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\EquipmentHistory;

#[Layout('layouts.app')]
class InfrastructureManager extends Component
{
    public Entity $entity;
    public ?int $selectedRoomId = null;

    // Room Modal State
    public bool $showRoomModal = false;
    public bool $isEditingRoom = false;
    public ?int $roomId = null;
    public $room_name;
    public $room_description;

    // Equipment Modal State
    public bool $showEquipmentModal = false;
    public bool $isEditingEquipment = false;
    public ?int $equipmentId = null;
    
    // Equipment Form Fields
    public $eq_name;
    public $category_id;
    public $type_id;
    public $nominal_power_w;
    public $avg_daily_use_hours;
    public $is_standby = false;
    public $cantidad = 1; // Para creación múltiple
    public $intensity = 'media';
    public $energy_label;

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
        $this->ensureSystemRooms();
        
        // Select first room by default
        if ($this->rooms->isNotEmpty()) {
            $this->selectedRoomId = $this->rooms->first()->id;
        }
    }

    private function ensureSystemRooms()
    {
        $systemRooms = ['Portátiles', 'Temporales'];
        foreach ($systemRooms as $name) {
            $this->entity->rooms()->firstOrCreate(
                ['name' => $name],
                ['description' => "Área protegida del sistema: {$name}"]
            );
        }
    }

    #[Computed]
    public function rooms()
    {
        return $this->entity->rooms()->orderBy('name')->get();
    }

    #[Computed]
    public function selectedRoom()
    {
        return $this->selectedRoomId ? Room::find($this->selectedRoomId) : null;
    }

    #[Computed]
    public function equipment()
    {
        if (!$this->selectedRoomId) return collect();
        
        return Equipment::where('room_id', $this->selectedRoomId)
            ->where('is_active', true)
            ->with(['category', 'type'])
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return EquipmentCategory::all();
    }

    #[Computed]
    public function types()
    {
        if (!$this->category_id) return collect();
        return EquipmentType::where('category_id', $this->category_id)->get();
    }

    public function selectRoom($id)
    {
        $this->selectedRoomId = $id;
    }

    // --- Room CRUD ---

    public function openRoomModal($id = null)
    {
        $this->resetErrorBag();
        if ($id) {
            $room = Room::findOrFail($id);
            $this->roomId = $room->id;
            $this->isEditingRoom = true;
            $this->room_name = $room->name;
            $this->room_description = $room->description;
        } else {
            $this->roomId = null;
            $this->isEditingRoom = false;
            $this->room_name = '';
            $this->room_description = '';
        }
        $this->showRoomModal = true;
    }

    public function saveRoom()
    {
        $this->validate([
            'room_name' => 'required|string|max:255',
            'room_description' => 'nullable|string',
        ]);

        if ($this->isEditingRoom) {
            $room = Room::findOrFail($this->roomId);
            if ($room->isSystemRoom()) {
                session()->flash('error', 'No se puede renombrar una habitación del sistema.');
                return;
            }
            $room->update(['name' => $this->room_name, 'description' => $this->room_description]);
        } else {
            $this->entity->rooms()->create(['name' => $this->room_name, 'description' => $this->room_description]);
        }

        $this->showRoomModal = false;
    }

    public function deleteRoom($id)
    {
        $room = Room::findOrFail($id);
        if ($room->isSystemRoom()) {
            session()->flash('error', 'No se puede eliminar una habitación del sistema.');
            return;
        }
        $room->delete();
        if ($this->selectedRoomId == $id) {
            $this->selectedRoomId = $this->rooms->first()?->id;
        }
    }

    // --- Equipment CRUD ---

    public function openEquipmentModal($id = null)
    {
        $this->resetErrorBag();
        if ($id) {
            $eq = Equipment::findOrFail($id);
            $this->equipmentId = $eq->id;
            $this->isEditingEquipment = true;
            $this->eq_name = $eq->name;
            $this->category_id = $eq->category_id;
            $this->type_id = $eq->type_id;
            $this->nominal_power_w = $eq->nominal_power_w;
            $this->avg_daily_use_hours = $eq->avg_daily_use_hours;
            $this->is_standby = (bool)$eq->is_standby;
            $this->intensity = $eq->intensity ?? 'media';
            $this->energy_label = $eq->energy_label;
            $this->cantidad = 1;
        } else {
            $this->equipmentId = null;
            $this->isEditingEquipment = false;
            $this->eq_name = '';
            $this->category_id = null;
            $this->type_id = null;
            $this->nominal_power_w = null;
            $this->avg_daily_use_hours = null;
            $this->is_standby = false;
            $this->cantidad = 1;
            $this->intensity = 'media';
            $this->energy_label = null;
        }
        $this->showEquipmentModal = true;
    }

    public function updatedTypeId($value)
    {
        if ($value) {
            $type = EquipmentType::find($value);
            if ($type) {
                $this->nominal_power_w = $type->default_power_watts;
                $this->avg_daily_use_hours = $type->default_avg_daily_use_hours;
                if (!$this->eq_name) $this->eq_name = $type->name;
            }
        }
    }

    public function saveEquipment()
    {
        $this->validate([
            'eq_name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'nominal_power_w' => 'required|numeric|min:1',
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        $data = [
            'name' => $this->eq_name,
            'category_id' => $this->category_id,
            'type_id' => $this->type_id,
            'nominal_power_w' => $this->nominal_power_w,
            'avg_daily_use_hours' => $this->avg_daily_use_hours ?? 0,
            'is_standby' => $this->is_standby,
            'intensity' => $this->intensity,
            'energy_label' => $this->energy_label,
            'is_active' => true,
            'is_validated' => true,
        ];

        if ($this->isEditingEquipment) {
            Equipment::findOrFail($this->equipmentId)->update($data);
        } else {
            for ($i = 0; $i < $this->cantidad; $i++) {
                $this->selectedRoom->equipment()->create($data);
            }
        }

        $this->showEquipmentModal = false;
    }

    public function deleteEquipment($id)
    {
        $eq = Equipment::findOrFail($id);
        $eq->update(['is_active' => false]);
        
        EquipmentHistory::create([
            'equipment_id' => $eq->id,
            'action' => 'baja',
            'reason' => 'Baja desde el gestor de infraestructura',
            'action_date' => now(),
            'user_id' => auth()->id(),
        ]);
        
        session()->flash('success', 'Equipo dado de baja.');
    }

    public function render()
    {
        $typePrefix = config("entity_types.{$this->entity->type}.route_prefix", 'entities.home');
        return view('livewire.physical.infrastructure-manager', [
            'route_prefix' => $typePrefix
        ]);
    }
}
