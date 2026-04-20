<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- STEP 1: INPUT --}}
        @if($step == 1)
            <x-card class="border-0 shadow-xl overflow-hidden max-w-2xl mx-auto" wire:transition>
                <div class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-6">
                        <i class="bi bi-airplane-engines text-5xl text-blue-600"></i>
                    </div>
                    
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Modo Vacaciones</h2>
                    
                    <p class="text-gray-600 text-lg mb-8 max-w-xl mx-auto">
                        El Asistente de Salida te ayudará a preparar tu casa para ahorrar energía y mantenerla segura mientras no estás.
                    </p>

                    <form wire:submit.prevent="calculate" class="max-w-sm mx-auto">
                        <div class="mb-8">
                            <label for="days" class="block text-xl font-medium text-gray-700 mb-4">
                                ¿Por cuántos días te vas?
                            </label>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    wire:model="days"
                                    class="block w-full text-center text-4xl font-bold text-blue-600 border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 p-4" 
                                    id="days" 
                                    placeholder="0" 
                                    required 
                                    min="1" 
                                    autofocus
                                >
                                <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">días</span>
                            </div>
                            @error('days') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-3">
                            <x-button type="submit" variant="primary" size="lg" class="w-full justify-center text-lg py-4">
                                <i class="bi bi-magic mr-2"></i> Generar Plan de Ahorro
                            </x-button>
                            
                            <a href="{{ route($config['route_prefix'] . '.show', $entity->id) }}" class="block w-full text-center text-gray-500 hover:text-gray-700 font-medium transition-colors">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex items-center justify-center gap-2 text-sm text-gray-500">
                    <i class="bi bi-info-circle"></i>
                    <span>Ahorra hasta un 30% en tu factura durante tu ausencia.</span>
                </div>
            </x-card>
        @endif

        {{-- STEP 2: RESULTS --}}
        @if($step == 2)
            <div wire:transition>
                {{-- Header --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="bg-linear-to-br from-sky-500 to-blue-700 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                            <i class="bi bi-airplane text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Plan de Vacaciones</h1>
                            <p class="text-gray-500">{{ $entity->name }} · {{ $days }} días</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <x-button variant="secondary" wire:click="resetPlan">
                            <i class="bi bi-arrow-left mr-2"></i> Cambiar Días
                        </x-button>
                        <x-button wire:click="confirm" variant="primary">
                            <i class="bi bi-check-lg mr-2"></i> Confirmar Viaje
                        </x-button>
                    </div>
                </div>

                {{-- Savings Banner --}}
                @if($result['total_savings'] > 0)
                <div class="bg-linear-to-r from-emerald-500 to-teal-600 rounded-2xl p-6 text-white text-center mb-8 shadow-lg">
                    <p class="text-emerald-100 text-sm font-medium uppercase tracking-wide mb-1">Ahorro potencial si seguís esta lista</p>
                    <p class="text-5xl font-bold">${{ number_format($result['total_savings'], 0, ',', '.') }}</p>
                    <p class="text-emerald-100 text-sm mt-2">
                        Tarifa usada: ${{ number_format($result['tariff_used'], 1) }}/kWh
                        (basada en tu última factura)
                    </p>
                </div>
                @endif

                {{-- Checklist --}}
                <div class="space-y-4 mb-8">
                    @foreach($result['checklist'] as $item)
                        @php
                            $cardStyle = match($item['category']) {
                                'critical'       => 'bg-red-50 border-red-200',
                                'security'       => 'bg-gray-50 border-gray-200',
                                'recommendation' => 'bg-amber-50 border-amber-200',
                                default          => 'bg-emerald-50 border-emerald-200',
                            };
                            $iconColor = match($item['color']) {
                                'danger'  => 'text-red-500',
                                'warning' => 'text-amber-500',
                                'success' => 'text-emerald-500',
                                'info'    => 'text-sky-500',
                                default   => 'text-gray-500',
                            };
                            $badgeText = match($item['category']) {
                                'critical'       => 'Crítico',
                                'security'       => 'Seguridad',
                                'recommendation' => 'Recomendación',
                                default          => 'Ahorro',
                            };
                            $badgeColor = match($item['category']) {
                                'critical'       => 'bg-red-100 text-red-700',
                                'security'       => 'bg-gray-200 text-gray-700',
                                'recommendation' => 'bg-amber-100 text-amber-700',
                                default          => 'bg-emerald-100 text-emerald-700',
                            };
                        @endphp
                        <div class="bg-white border {{ $cardStyle }} rounded-2xl p-5 flex items-start gap-4 shadow-sm hover:shadow-md transition-shadow">
                            {{-- Checkbox --}}
                            <input type="checkbox" class="mt-1 w-5 h-5 rounded accent-emerald-500 cursor-pointer shrink-0">

                            {{-- Icon --}}
                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                <i class="bi {{ $item['icon'] }} text-xl {{ $iconColor }}"></i>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <h3 class="font-semibold text-gray-900">{{ $item['title'] }}</h3>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $badgeColor }}">{{ $badgeText }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">{{ $item['description'] }}</p>
                                <span class="inline-block text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-lg
                                    {{ $item['category'] === 'critical' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $item['action'] }}
                                </span>
                            </div>

                            {{-- Savings --}}
                            @if(isset($item['savings']) && $item['savings'] > 0)
                                <div class="text-right shrink-0">
                                    <p class="text-xs text-gray-400">Ahorro</p>
                                    <p class="text-lg font-bold text-emerald-600">+${{ number_format($item['savings'], 0, ',', '.') }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Tips --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-alert type="info">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-lightbulb text-xl"></i>
                            <div>
                                <strong>Tip Pro:</strong> ¿Dejás luces por seguridad? Una luz fija las 24hs delata que no estás. Usá focos inteligentes.
                            </div>
                        </div>
                    </x-alert>
                    <x-alert type="warning">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-shield-check text-xl"></i>
                            <div>
                                <strong>Antes de cerrar:</strong>
                                <ul class="mt-1 space-y-0.5 text-sm">
                                    <li>✓ Cerrar llave de paso de agua</li>
                                    <li>✓ Cerrar llave de gas</li>
                                    <li>✓ Tirar la basura</li>
                                </ul>
                            </div>
                        </div>
                    </x-alert>
                </div>
            </div>
        @endif
    </div>
</div>
