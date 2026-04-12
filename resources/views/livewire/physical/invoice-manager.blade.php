<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-emerald-500 to-teal-700 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-receipt text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Facturas</h1>
                    <p class="text-gray-500">{{ $entity->name }} — Registro histórico de consumos</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                @if($this->contract)
                    <x-button variant="primary" wire:click="create" class="bg-emerald-600 hover:bg-emerald-700 shadow-md">
                        <i class="bi bi-plus-circle mr-2"></i> Nueva Factura
                    </x-button>
                @endif
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        @if(session('success'))
            <x-alert type="success" class="mb-6" wire:transition>
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="error" class="mb-6" wire:transition>
                {{ session('error') }}
            </x-alert>
        @endif

        @if(!$this->contract)
            <div class="bg-amber-50 rounded-3xl p-8 border border-amber-100 text-center">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-600">
                    <i class="bi bi-exclamation-triangle text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-amber-900">Sin Contrato Activo</h3>
                <p class="text-amber-700 max-w-md mx-auto mt-2 mb-6">
                    No puedes cargar facturas sin antes registrar un contrato o medidor activo para esta propiedad.
                </p>
                <x-button variant="primary" href="{{ route($config['route_prefix'] . '.contracts.create', $entity->id) }}" class="bg-amber-600 hover:bg-amber-700 border-none">
                    Registrar Contrato Ahora
                </x-button>
            </div>
        @else
            <div wire:loading.class="opacity-50 transition-opacity" class="transition-opacity">
                
                {{-- Stats Summary --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-3xl shadow-xs border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Registros</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ $this->invoices->count() }}</h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl shadow-xs border border-gray-100">
                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">Consumo Total</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ number_format($this->invoices->sum('total_energy_consumed_kwh'), 0) }} <span class="text-sm font-normal text-gray-400">kWh</span></h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl shadow-xs border border-gray-100">
                        <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-1">Importe Histórico</p>
                        <h3 class="text-2xl font-black text-gray-900">${{ number_format($this->invoices->sum('total_amount'), 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl shadow-xs border border-gray-100">
                        <p class="text-[10px] font-black text-purple-400 uppercase tracking-widest mb-1">Promedio Mensual</p>
                        <h3 class="text-2xl font-black text-gray-900">${{ number_format($this->invoices->avg('total_amount'), 0, ',', '.') }}</h3>
                    </div>
                </div>

                {{-- Invoices Table --}}
                <div class="bg-white rounded-3xl shadow-xs border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Factura # / Fecha</th>
                                    <th class="px-6 py-4">Período de Consumo</th>
                                    <th class="px-6 py-4">Energía (kWh)</th>
                                    <th class="px-6 py-4">Total Importe</th>
                                    <th class="px-6 py-4">Estado / Ajuste</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($this->invoices as $invoice)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900">#{{ $invoice->invoice_number }}</span>
                                                <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">{{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/y') }}</span>
                                                <i class="bi bi-arrow-right text-[10px] text-gray-300"></i>
                                                <span class="font-medium">{{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/y') }}</span>
                                                <span class="text-[9px] bg-gray-100 px-1.5 py-0.5 rounded-sm font-black text-gray-400 ml-1">{{ $invoice->days_in_period }}D</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-baseline gap-1">
                                                <span class="font-black text-gray-900 text-lg">{{ number_format($invoice->total_energy_consumed_kwh, 0) }}</span>
                                                <span class="text-[10px] text-gray-400 font-bold">kWh</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-black text-emerald-600 text-lg">${{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($invoice->usage_locked)
                                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                                    <span class="w-1 h-1 rounded-full bg-emerald-600"></span> Calibrado
                                                </span>
                                            @elseif($invoice->usageAdjustment?->adjusted)
                                                <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                                    <span class="w-1 h-1 rounded-full bg-blue-600"></span> Ajustado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                                    <span class="w-1 h-1 rounded-full bg-gray-400"></span> Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="edit({{ $invoice->id }})" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button onclick="confirm('¿Seguro que deseas eliminar esta factura?') || event.stopImmediatePropagation()" wire:click="delete({{ $invoice->id }})" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-20 text-center">
                                            <div class="max-w-xs mx-auto">
                                                <i class="bi bi-journal-x text-4xl text-gray-200 mb-4 block"></i>
                                                <p class="font-bold text-gray-400 uppercase tracking-widest text-xs">Sin facturas registradas</p>
                                                <p class="text-gray-400 text-xs mt-2">Carga tu primera factura para empezar a ver el consumo.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Invoice Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs" wire:transition>
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden" @click.away="$wire.set('showModal', false)">
                <div class="bg-linear-to-r {{ $isEditing ? 'from-blue-600 to-indigo-700' : 'from-emerald-600 to-teal-700' }} px-8 py-6 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <i class="bi {{ $isEditing ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                            {{ $isEditing ? 'Editar Factura' : 'Cargar Nueva Factura' }}
                        </h3>
                        <p class="text-white/70 text-xs mt-1 uppercase font-bold tracking-widest">{{ $entity->name }}</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-white/80 hover:text-white">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="save" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Basic Info --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Número de Factura</label>
                                <input type="text" wire:model="invoice_number" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Ej: 001-23456">
                                @error('invoice_number') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Fecha de Factura</label>
                                <input type="date" wire:model="invoice_date" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            </div>
                        </div>

                        {{-- Period Info --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Desde</label>
                                <input type="date" wire:model="start_date" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                @error('start_date') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Hasta</label>
                                <input type="date" wire:model="end_date" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                @error('end_date') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Consumption --}}
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Consumo Total (kWh)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" wire:model="total_energy_consumed_kwh" class="w-full bg-white border-gray-100 rounded-xl pl-4 pr-12 py-3 text-lg font-black text-gray-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-gray-400 text-xs lowercase">kWh</span>
                                </div>
                                @error('total_energy_consumed_kwh') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Importe Total ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-emerald-600 text-lg">$</span>
                                    <input type="number" step="0.01" wire:model="total_amount" class="w-full bg-white border-gray-100 rounded-xl pl-10 pr-4 py-3 text-lg font-black text-emerald-700 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                                </div>
                                @error('total_amount') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Advanced Toggle --}}
                    <div class="mt-8 border-t border-gray-100 pt-6">
                        <button type="button" wire:click="toggleAdvanced" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 flex items-center gap-2">
                            <i class="bi {{ $showAdvanced ? 'bi-dash-circle' : 'bi-plus-circle' }}"></i>
                            {{ $showAdvanced ? 'Ocultar Desglose de Gastos' : 'Añadir Desglose de Gastos (Opcional)' }}
                        </button>
                        
                        @if($showAdvanced)
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4" wire:transition>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Energía</label>
                                    <input type="number" wire:model="cost_for_energy" class="w-full bg-gray-50 border-gray-100 rounded-lg px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Potencia</label>
                                    <input type="number" wire:model="cost_for_power" class="w-full bg-gray-50 border-gray-100 rounded-lg px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Impuestos</label>
                                    <input type="number" wire:model="taxes" class="w-full bg-gray-50 border-gray-100 rounded-lg px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Otros</label>
                                    <input type="number" wire:model="other_charges" class="w-full bg-gray-50 border-gray-100 rounded-lg px-3 py-2 text-xs">
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-10 flex flex-col md:flex-row gap-3">
                        <x-button type="submit" variant="primary" class="flex-1 justify-center py-4 bg-emerald-600 hover:bg-emerald-700 shadow-lg">
                            <i class="bi bi-check-circle mr-2"></i> {{ $isEditing ? 'Guardar Cambios' : 'Cargar Factura' }}
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
