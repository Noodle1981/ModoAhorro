<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-indigo-500 to-indigo-700 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-file-earmark-text text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Contratos</h1>
                    <p class="text-gray-500">Administra medidores, tarifas y proveedores</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="primary" wire:click="create" class="bg-indigo-600 hover:bg-indigo-700 shadow-md">
                    <i class="bi bi-plus-circle mr-2"></i> Nuevo Contrato
                </x-button>
                @if($entityId)
                    @php $entity = \App\Models\Entity::find($entityId); @endphp
                    <x-button variant="secondary" href="{{ route(config('entity_types.' . $entity->type . '.route_prefix') . '.show', $entity->id) }}">
                        <i class="bi bi-arrow-left mr-2"></i> Volver a {{ $entity->name }}
                    </x-button>
                @else
                    <x-button variant="secondary" href="{{ route('dashboard') }}">
                        <i class="bi bi-arrow-left mr-2"></i> Dashboard
                    </x-button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <x-alert type="success" class="mb-6" wire:transition>{{ session('success') }}</x-alert>
        @endif

        <div wire:loading.class="opacity-50 transition-opacity" class="transition-opacity">
            @if($this->contracts->isEmpty())
                <div class="bg-white rounded-3xl p-16 border border-gray-100 text-center shadow-sm">
                    <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-file-earmark-plus text-4xl text-indigo-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No hay contratos registrados</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-2 mb-8 text-sm">
                        Registra tu primer contrato de suministro eléctrico para habilitar la carga de facturas y análisis de ahorro.
                    </p>
                    <x-button variant="primary" wire:click="create" class="bg-indigo-600 hover:bg-indigo-700">
                        Comenzar Ahora
                    </x-button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->contracts as $contract)
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-xs hover:shadow-md transition-all overflow-hidden relative group">
                            {{-- Status Ribbon --}}
                            <div class="absolute top-0 right-0 p-4">
                                <button wire:click="toggleActive({{ $contract->id }})" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter transition-colors {{ $contract->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400 hover:bg-emerald-100 hover:text-emerald-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $contract->is_active ? 'bg-emerald-600 animate-pulse' : 'bg-gray-300' }}"></span>
                                    {{ $contract->is_active ? 'Activo' : 'Activar' }}
                                </button>
                            </div>

                            <div class="p-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                                        <i class="bi bi-building-fill text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-gray-900 leading-tight">{{ $contract->entity->name }}</h4>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $contract->proveedor->name }}</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between text-xs py-2 border-b border-gray-50">
                                        <span class="text-gray-400 font-bold uppercase tracking-tighter">N° Suministro</span>
                                        <span class="font-mono font-black text-gray-700">{{ $contract->supply_number }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs py-2 border-b border-gray-50">
                                        <span class="text-gray-400 font-bold uppercase tracking-tighter">Tarifa Aplicada</span>
                                        <span class="font-black text-gray-700">{{ $contract->rate_name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs py-2 border-b border-gray-50">
                                        <span class="text-gray-400 font-bold uppercase tracking-tighter">Potencia (P1)</span>
                                        <span class="font-black text-gray-900">{{ number_format($contract->contracted_power_kw_p1, 1) }} <span class="text-[10px] font-normal text-gray-400">kW</span></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs py-2">
                                        <span class="text-gray-400 font-bold uppercase tracking-tighter">Tipo Suministro</span>
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-[9px] font-black text-gray-500 uppercase">
                                            {{ $contract->is_three_phase ? 'Trifásico' : 'Monofásico' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-8 flex gap-2">
                                    <button wire:click="edit({{ $contract->id }})" class="flex-1 bg-gray-50 hover:bg-indigo-50 text-gray-500 hover:text-indigo-600 py-2.5 rounded-xl text-xs font-bold transition-colors border border-gray-100">
                                        <i class="bi bi-pencil-square mr-1.5"></i> Editar
                                    </button>
                                    <button onclick="confirm('¿Eliminar este contrato? Esto desconectará todas las facturas asociadas.') || event.stopImmediatePropagation()" wire:click="delete({{ $contract->id }})" class="w-12 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 py-2.5 rounded-xl transition-colors border border-gray-100">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 pt-8 border-t border-gray-200">
                    <div class="flex items-center gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-xs">
                        <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600">
                            <i class="bi bi-layers-fill text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Contratos</p>
                            <h4 class="text-xl font-black text-gray-900">{{ $this->contracts->count() }}</h4>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-xs">
                        <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600">
                            <i class="bi bi-patch-check-fill text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Suministros Activos</p>
                            <h4 class="text-xl font-black text-gray-900">{{ $this->contracts->where('is_active', true)->count() }}</h4>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-xs">
                        <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-600">
                            <i class="bi bi-lightning-charge-fill text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Potencia Administrada</p>
                            <h4 class="text-xl font-black text-gray-900">{{ number_format($this->contracts->where('is_active', true)->sum('contracted_power_kw_p1'), 1) }} <span class="text-sm font-normal text-gray-400">kW</span></h4>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Contract Modal --}}
        @if($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs" wire:transition>
                <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden" @click.away="$wire.set('showModal', false)">
                    <div class="bg-linear-to-r {{ $isEditing ? 'from-indigo-600 to-blue-700' : 'from-emerald-600 to-teal-700' }} px-8 py-6 text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold flex items-center gap-2">
                                <i class="bi {{ $isEditing ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                                {{ $isEditing ? 'Editar Especificaciones' : 'Nuevo Registro de Suministro' }}
                            </h3>
                            <p class="text-white/70 text-[10px] font-black uppercase tracking-widest mt-1">Configuración técnica de red</p>
                        </div>
                        <button wire:click="$set('showModal', false)" class="text-white/80 hover:text-white">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="save" class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Entity & Provider --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Propiedad / Entidad</label>
                                    <select wire:model="selected_entity_id" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                        <option value="">Selecciona una entidad...</option>
                                        @foreach($this->entities as $entity)
                                            <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selected_entity_id') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Distribuidora / Proveedor</label>
                                    <select wire:model="proveedor_id" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                        <option value="">Selecciona un proveedor...</option>
                                        @foreach($this->proveedores as $prov)
                                            <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('proveedor_id') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Numbers --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Número de Suministro (NIU)</label>
                                    <input type="text" wire:model="supply_number" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Ej: 14002345">
                                    @error('supply_number') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tipo de Tarifa</label>
                                    <input type="text" wire:model="rate_name" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Ej: T1 Residencial / T2">
                                    @error('rate_name') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Technical Grid --}}
                            <div class="md:col-span-2 p-6 bg-gray-50 rounded-3xl border border-gray-100">
                                <div class="flex items-center justify-between mb-4">
                                    <h6 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Parámetros Técnicos de Red</h6>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" wire:model="is_three_phase" class="sr-only">
                                            <div class="w-10 h-5 bg-gray-200 rounded-full shadow-inner transition-colors {{ $is_three_phase ? 'bg-indigo-500' : '' }}"></div>
                                            <div class="dot absolute left-1 top-1 bg-white w-3 h-3 rounded-full transition-transform {{ $is_three_phase ? 'translate-x-5' : '' }}"></div>
                                        </div>
                                        <div class="ml-3 text-[10px] font-bold text-gray-500 uppercase">Trifásico</div>
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 mb-1">POTENCIA P1 (kW)</label>
                                        <input type="number" step="0.1" wire:model="contracted_power_kw_p1" class="w-full bg-white border-gray-200 rounded-xl px-4 py-2 text-sm font-black">
                                        @error('contracted_power_kw_p1') <span class="text-[9px] text-red-500 font-bold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 mb-1">POTENCIA P2 (Opcional)</label>
                                        <input type="number" step="0.1" wire:model="contracted_power_kw_p2" class="w-full bg-white border-gray-200 rounded-xl px-4 py-2 text-sm font-black">
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 mb-1">POTENCIA P3 (Opcional)</label>
                                        <input type="number" step="0.1" wire:model="contracted_power_kw_p3" class="w-full bg-white border-gray-200 rounded-xl px-4 py-2 text-sm font-black">
                                    </div>
                                </div>
                            </div>

                            {{-- Options --}}
                            <div class="flex items-center gap-6 md:col-span-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="w-5 h-5 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20">
                                    <span class="ml-2 text-xs font-bold text-gray-600">Este es el contrato activo actualmente</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col md:flex-row gap-3">
                            <x-button type="submit" variant="primary" class="flex-1 justify-center py-4 bg-indigo-600 hover:bg-indigo-700 shadow-lg">
                                <i class="bi bi-check-circle mr-2"></i> {{ $isEditing ? 'Guardar Cambios' : 'Registrar Contrato' }}
                            </x-button>
                            <x-button type="button" variant="secondary" wire:click="$set('showModal', false)" class="justify-center py-4">
                                Cancelar
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
