<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-emerald-500 to-teal-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-arrow-repeat text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Catálogo de Reemplazos</h1>
                    <p class="text-gray-500">Oportunidades de ahorro para {{ $entity->name }}</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        @if(session('success'))
            <x-alert type="success" class="mb-6" wire:transition>{{ session('success') }}</x-alert>
        @endif

        <div wire:loading.class="opacity-50 transition-opacity" class="transition-opacity">
            @if(count($this->opportunities) > 0)
                {{-- Opportunities Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($this->opportunities as $op)
                        @php
                            $colorMap = [
                                'success' => 'emerald',
                                'warning' => 'amber',
                                'danger' => 'red',
                                'info' => 'blue',
                            ];
                            $color = $colorMap[$op['verdict']['color']] ?? 'gray';
                        @endphp
                        <div class="bg-white rounded-2xl shadow-sm border-l-4 border-l-{{ $color === 'emerald' ? 'emerald' : ($color === 'amber' ? 'amber' : ($color === 'red' ? 'red' : 'blue')) }}-500 overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="p-6">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full bg-{{ $color === 'emerald' ? 'emerald' : ($color === 'amber' ? 'amber' : ($color === 'red' ? 'red' : 'blue')) }}-100 text-{{ $color === 'emerald' ? 'emerald' : ($color === 'amber' ? 'amber' : ($color === 'red' ? 'red' : 'blue')) }}-700">
                                            {{ $op['verdict']['label'] }}
                                        </span>
                                        <h3 class="text-lg font-bold text-gray-900 mt-2 truncate">{{ $op['equipment_name'] }}</h3>
                                        <p class="text-sm text-gray-500">
                                            Consumo: <strong>{{ $op['current_consumption_kwh'] }} kWh</strong>
                                            @if($op['is_estimated'] ?? false)
                                                <span class="ml-1 text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-sm">estimado</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center shrink-0 border border-gray-100">
                                        <i class="bi bi-plug text-2xl text-gray-400"></i>
                                    </div>
                                </div>

                                {{-- Stats --}}
                                <div class="grid grid-cols-2 gap-4 py-4 border-y border-gray-100 bg-gray-50/30 -mx-6 px-6">
                                    <div class="text-center">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Ahorro Mensual</p>
                                        <p class="text-xl font-bold text-emerald-600">
                                            ${{ number_format($op['monthly_savings_amount'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">ROI</p>
                                        <p class="text-xl font-bold text-gray-900">{{ $op['payback_months'] }} <span class="text-xs font-normal text-gray-400">ms</span></p>
                                    </div>
                                </div>

                                {{-- Suggestion --}}
                                <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-100">
                                    <p class="text-xs text-gray-700 leading-tight">
                                        <i class="bi bi-lightbulb-fill text-amber-500 mr-1"></i>
                                        Reemplazo sugerido: <strong>{{ $op['replacement_suggestion'] }}</strong>
                                    </p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-2 mt-5">
                                    <x-button variant="secondary" size="sm" wire:click="openRefineModal({{ $op['equipment_id'] }})" class="flex-1 justify-center">
                                        <i class="bi bi-pencil mr-1"></i> Refinar
                                    </x-button>
                                    <x-button variant="primary" size="sm" href="{{ $op['affiliate_link'] ?? '#' }}" target="_blank" class="flex-1 justify-center">
                                        <i class="bi bi-cart mr-1"></i> Comprar
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <x-card class="text-center py-20 mb-8 border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-check-circle text-4xl text-emerald-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Todo Optimizado!</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        Tus equipos actuales son eficientes o no hay suficientes datos para una recomendación.
                    </p>
                    <x-button variant="secondary" wire:click="$refresh" class="mt-6">
                        <i class="bi bi-arrow-clockwise mr-2"></i> Recargar Análisis
                    </x-button>
                </x-card>
            @endif

            {{-- Refinement Table --}}
            <x-card :padding="false">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="bi bi-cpu text-blue-500"></i>
                            Mis Equipos Analizados
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Carga el año y etiqueta para mejorar la precisión del ROI</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-widest font-bold">
                            <tr>
                                <th class="px-6 py-4">Equipo</th>
                                <th class="px-6 py-4">Categoría</th>
                                <th class="px-6 py-4">Año / Etiqueta / Tecnología</th>
                                <th class="px-6 py-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($this->analyzableEquipments as $eq)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-900">{{ $eq->name }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $eq->room->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $eq->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 text-gray-600">
                                            @if($eq->acquisition_year)
                                                <span class="flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded-sm border border-blue-100">
                                                    <i class="bi bi-calendar3"></i> {{ $eq->acquisition_year }}
                                                </span>
                                            @endif
                                            @if($eq->energy_label)
                                                <span class="flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-sm border border-emerald-100">
                                                    <i class="bi bi-tag"></i> {{ $eq->energy_label }}
                                                </span>
                                            @endif
                                            @if($eq->is_inverter)
                                                <span class="flex items-center gap-1 bg-purple-50 text-purple-700 px-2 py-0.5 rounded-sm border border-purple-100">
                                                    <i class="bi bi-lightning-charge"></i> Inverter
                                                </span>
                                            @endif
                                            @if(!$eq->acquisition_year && !$eq->energy_label)
                                                <span class="text-gray-300 italic">Sin datos técnicos</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button wire:click="openRefineModal({{ $eq->id }})" class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 justify-center w-full">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Refine Modal --}}
        @if($showRefineModal && $editingEquipment)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs transition-all" wire:transition>
                <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden">
                    <div class="bg-linear-to-r from-blue-600 to-indigo-700 px-6 py-4 text-white flex justify-between items-center">
                        <h3 class="font-bold flex items-center gap-2">
                            <i class="bi bi-pencil-square"></i>
                            Refinar: {{ $editingEquipment->name }}
                        </h3>
                        <button wire:click="closeModal" class="text-white/80 hover:text-white transition-colors">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Año de Adquisición</label>
                                <input type="number" wire:model="acquisition_year" 
                                    class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-xs"
                                    placeholder="Ej: 2015">
                                @error('acquisition_year') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Etiqueta Energética</label>
                                <select wire:model="energy_label" 
                                    class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-xs">
                                    <option value="">Seleccionar...</option>
                                    @foreach(['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E'] as $label)
                                        <option value="{{ $label }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Capacidad / Potencia</label>
                                <input type="number" wire:model="capacity" 
                                    class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-xs"
                                    placeholder="Ej: 3000">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Unidad</label>
                                <select wire:model="capacity_unit" 
                                    class="w-full rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-xs">
                                    <option value="">Seleccionar...</option>
                                    <option value="frigorias">Frigorías</option>
                                    <option value="litros">Litros</option>
                                    <option value="kg">Kilogramos</option>
                                    <option value="btu">BTU</option>
                                    <option value="watts">Watts</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-4 bg-purple-50 rounded-2xl border border-purple-100">
                            <input type="checkbox" wire:model="is_inverter" id="inverter_check" 
                                class="w-5 h-5 rounded text-purple-600 focus:ring-purple-500 border-gray-300">
                            <label for="inverter_check" class="flex-1 cursor-pointer">
                                <p class="text-sm font-bold text-purple-900">Tecnología Inverter</p>
                                <p class="text-[10px] text-purple-600">Ahorra hasta un 40% adicional de energía</p>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-8 py-5 flex items-center justify-end gap-3 border-t border-gray-100">
                        <button wire:click="closeModal" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </button>
                        <x-button wire:click="saveRefinement" variant="primary">
                            <i class="bi bi-save mr-2"></i> Actualizar Análisis
                        </x-button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Info Alert --}}
        <x-alert type="info" class="mt-8">
            <div class="flex items-start gap-4">
                <div class="bg-teal-100 p-2 rounded-lg">
                    <i class="bi bi-info-circle-fill text-teal-600"></i>
                </div>
                <div>
                    <strong class="text-teal-900">¿Cómo calculamos el ROI?</strong>
                    <p class="text-teal-800 text-sm mt-1">
                        Utilizamos datos de consumo de tus facturas y los comparamos con benchmarks de equipos modernos (Inverter Clase A). 
                        El refinamiento de los datos ayuda a determinar si el equipo actual ya es eficiente o si el reemplazo se paga solo en menos tiempo.
                    </p>
                </div>
            </div>
        </x-alert>
    </div>
</div>
