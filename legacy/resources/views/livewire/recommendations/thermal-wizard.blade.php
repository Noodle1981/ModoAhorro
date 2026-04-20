<div class="min-h-screen bg-linear-to-br from-blue-50 via-white to-cyan-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-linear-to-br from-blue-500 to-cyan-600 rounded-2xl text-white shadow-lg mb-4">
                <i class="bi bi-thermometer-half text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Diagnóstico Térmico</h1>
            <p class="text-gray-500">Evaluá la aislación de tu casa</p>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            {{-- Progress --}}
            <div class="bg-blue-100 h-1">
                <div class="bg-blue-600 h-1 transition-all" style="--progress-width: {{ $this->progress }}%; width: var(--progress-width);"></div>
            </div>

            <div class="p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="text-sm text-red-600 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    {{-- 1. Roof --}}
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-sm font-bold">1</span>
                            ¿Cómo es tu techo?
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="roof_type" value="sheet_metal_no_insulation" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                    <i class="bi bi-house text-2xl text-gray-400 peer-checked:text-blue-500"></i>
                                    <p class="font-medium text-gray-900 mt-2">Chapa</p>
                                    <p class="text-xs text-gray-500">Sin aislación</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="roof_type" value="concrete_slab" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                    <i class="bi bi-bricks text-2xl text-gray-400 peer-checked:text-blue-500"></i>
                                    <p class="font-medium text-gray-900 mt-2">Losa</p>
                                    <p class="text-xs text-gray-500">Hormigón</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="roof_type" value="insulated_panel" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                    <i class="bi bi-grid-3x3 text-2xl text-gray-400 peer-checked:text-blue-500"></i>
                                    <p class="font-medium text-gray-900 mt-2">Panel</p>
                                    <p class="text-xs text-gray-500">Aislado</p>
                                </div>
                            </label>
                        </div>
                        
                        <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer">
                            <input type="checkbox" wire:model.live="roof_insulation" class="rounded border-gray-300 text-blue-500 focus:ring-blue-500 w-5 h-5">
                            <div>
                                <span class="font-medium text-gray-900">¿Tiene aislación térmica?</span>
                                <p class="text-sm text-gray-500">Membrana, cielorraso con lana de vidrio, etc.</p>
                            </div>
                        </label>
                    </div>

                    {{-- 2. Windows --}}
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-sm font-bold">2</span>
                            ¿Cómo son tus ventanas?
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Tipo de Vidrio</p>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                        <input type="radio" wire:model.live="window_type" value="single_glass" class="text-blue-500 focus:ring-blue-500">
                                        <span>Vidrio Simple (Común)</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                        <input type="radio" wire:model.live="window_type" value="dvh" class="text-blue-500 focus:ring-blue-500">
                                        <span>Doble Vidrio (DVH)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Marcos</p>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                        <input type="radio" wire:model.live="window_frame" value="aluminum" class="text-blue-500 focus:ring-blue-500">
                                        <span>Metal / Aluminio</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                        <input type="radio" wire:model.live="window_frame" value="wood" class="text-blue-500 focus:ring-blue-500">
                                        <span>Madera</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                        <input type="radio" wire:model.live="window_frame" value="pvc" class="text-blue-500 focus:ring-blue-500">
                                        <span>PVC</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <label class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl cursor-pointer">
                            <input type="checkbox" wire:model.live="drafts_detected" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500 w-5 h-5">
                            <div>
                                <span class="font-medium text-amber-700"><i class="bi bi-wind mr-1"></i> ¿Sientes "chifletes"?</span>
                                <p class="text-sm text-amber-600">Entra aire por los bordes de las ventanas</p>
                            </div>
                        </label>
                    </div>

                    {{-- 3. Orientation --}}
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-sm font-bold">3</span>
                            Orientación y Ventilación
                        </h3>
                        
                        <p class="text-sm text-gray-500 mb-4">¿Cómo está orientada la fachada principal?</p>
                        
                        <div class="space-y-2 mb-4">
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <input type="radio" wire:model.live="orientation" value="norte_sur" class="text-blue-500 focus:ring-blue-500">
                                <div>
                                    <span class="font-medium">Eje Norte-Sur (Ideal)</span>
                                    <p class="text-xs text-gray-500">Sol en invierno / Fresco en verano</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <input type="radio" wire:model.live="orientation" value="este_oeste" class="text-blue-500 focus:ring-blue-500">
                                <div>
                                    <span class="font-medium">Eje Este-Oeste</span>
                                    <p class="text-xs text-gray-500">Sol bajo y caluroso por la mañana/tarde</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                <input type="radio" wire:model.live="orientation" value="diagonal" class="text-blue-500 focus:ring-blue-500">
                                <span class="font-medium">Diagonal / Otra</span>
                            </label>
                        </div>
                        
                        <label class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl cursor-pointer">
                            <input type="checkbox" wire:model.live="south_window" class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500 w-5 h-5">
                            <div>
                                <span class="font-medium text-emerald-700"><i class="bi bi-wind mr-1"></i> ¿Tenés ventanas al SUR?</span>
                                <p class="text-sm text-emerald-600">Para ingreso de aire fresco</p>
                            </div>
                        </label>
                    </div>

                    {{-- 4. Shade --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-sm font-bold">4</span>
                            Sombra y Entorno
                        </h3>
                        
                        <p class="text-sm text-gray-500 mb-4">¿Cuánta sombra recibe tu casa?</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="sun_exposure" value="high" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                    <i class="bi bi-sun text-2xl text-amber-400"></i>
                                    <p class="font-medium text-gray-900 mt-2">Ninguna</p>
                                    <p class="text-xs text-gray-500">Sol directo</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="sun_exposure" value="medium" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                    <i class="bi bi-cloud-sun text-2xl text-gray-400"></i>
                                    <p class="font-medium text-gray-900 mt-2">Parcial</p>
                                    <p class="text-xs text-gray-500">Algunas horas</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="sun_exposure" value="low" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                    <i class="bi bi-tree text-2xl text-emerald-500"></i>
                                    <p class="font-medium text-gray-900 mt-2">Mucha</p>
                                    <p class="text-xs text-gray-500">Muy protegido</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="text-center pt-6 border-t border-gray-200">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-8 py-4 bg-linear-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                            <span wire:loading.remove>Calcular Diagnóstico</span>
                            <span wire:loading>Calculando...</span>
                            <i class="bi bi-arrow-right ml-2" wire:loading.remove></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
