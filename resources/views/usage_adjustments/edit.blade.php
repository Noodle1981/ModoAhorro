@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ajuste de uso - Factura #{{ $invoice->id }}</h2>
    <p class="text-muted">
        Periodo: {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
        (Cantidad de días: {{ \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date)) }})
    </p>
    <form method="POST" action="{{ route('usage_adjustments.update', $invoice->id) }}">
        @csrf
        @forelse($rooms as $room)
            @if($room->equipment->isNotEmpty())
                <h4 class="mt-4">{{ $room->name }}</h4>
                <table class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Habitación</th>
                            <th>Potencia (W)</th>
                            <th>Frecuencia de uso</th>
                            <th>Horas uso diario (promedio)</th>
                            <th>Días de uso en el periodo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($room->equipment as $equipment)
                            @php $usage = $usages[$equipment->id] ?? null; @endphp
                            <tr>
                                <td>{{ $equipment->name }}</td>
                                <td>{{ $equipment->room->name ?? '-' }}</td>
                                <td>{{ $equipment->nominal_power_w ?? '-' }}</td>
                                <td>
                                    <select name="usages[{{ $equipment->id }}][usage_frequency]" class="form-select" onchange="toggleUsageFields{{ $equipment->id }}(this.value)">
                                        @php
                                            $freqs = ['diario' => 'Diario', 'semanal' => 'Semanal', 'quincenal' => 'Quincenal', 'mensual' => 'Mensual', 'puntual' => 'Puntual'];
                                            $selectedFreq = $usage->usage_frequency ?? 'diario';
                                        @endphp
                                        @foreach($freqs as $key => $label)
                                            <option value="{{ $key }}" {{ $selectedFreq == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div id="dailyFields_{{ $equipment->id }}" style="display: {{ ($selectedFreq == 'diario' || $selectedFreq == 'semanal') ? 'block' : 'none' }};">
                                        <input type="range" min="0" max="24" step="0.1" name="usages[{{ $equipment->id }}][avg_daily_use_hours]" value="{{ $usage->avg_daily_use_hours ?? 0 }}" class="form-range" oninput="updateHours{{ $equipment->id }}(this.value)">
                                        <input type="number" min="0" max="24" step="0.1" id="hours_{{ $equipment->id }}" name="usages[{{ $equipment->id }}][avg_daily_use_hours]" value="{{ $usage->avg_daily_use_hours ?? 0 }}" class="form-control d-inline-block w-auto ms-2" style="width:80px;" oninput="updateHours{{ $equipment->id }}(this.value)">
                                        <span id="minutes_{{ $equipment->id }}" class="ms-2 text-info">{{ isset($usage->avg_daily_use_hours) ? round($usage->avg_daily_use_hours * 60) : 0 }} min</span>
                                        <small class="text-muted d-block">Selecciona las horas promedio de uso diario/semanal. El valor se muestra también en minutos.</small>
                                    </div>
                                    <div id="occasionalFields_{{ $equipment->id }}" style="display: {{ ($selectedFreq != 'diario' && $selectedFreq != 'semanal') ? 'block' : 'none' }};">
                                        <input type="number" min="0" step="1" name="usages[{{ $equipment->id }}][usage_count]" value="{{ $usage->usage_count ?? '' }}" class="form-control d-inline-block w-auto" style="width:80px;" placeholder="Cantidad de usos">
                                        <input type="number" min="0" step="0.1" name="usages[{{ $equipment->id }}][avg_use_duration]" value="{{ $usage->avg_use_duration ?? '' }}" class="form-control d-inline-block w-auto ms-2" style="width:80px;" placeholder="Duración promedio (h)">
                                        <small class="text-muted d-block">Indica cuántas veces lo usaste y la duración promedio por uso (en horas).</small>
                                    </div>
                                    <script>
                                        function updateHours{{ $equipment->id }}(val) {
                                            document.getElementById('hours_{{ $equipment->id }}').value = val;
                                            document.getElementById('minutes_{{ $equipment->id }}').innerText = Math.round(val * 60) + ' min';
                                        }
                                        function toggleUsageFields{{ $equipment->id }}(freq) {
                                            document.getElementById('dailyFields_{{ $equipment->id }}').style.display = (freq === 'diario' || freq === 'semanal') ? 'block' : 'none';
                                            document.getElementById('occasionalFields_{{ $equipment->id }}').style.display = (freq === 'diario' || freq === 'semanal') ? 'none' : 'block';
                                        }
                                    </script>
                                </td>
                                <td>
                                    <div class="mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="all_days_{{ $equipment->id }}" onchange="toggleAllDays{{ $equipment->id }}(this.checked)">
                                            <label class="form-check-label fw-bold" for="all_days_{{ $equipment->id }}">
                                                ✓ Todos los días
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 day-checkboxes-{{ $equipment->id }}">
                                        @php
                                            $days = ['L' => 'Lunes', 'M' => 'Martes', 'X' => 'Miércoles', 'J' => 'Jueves', 'V' => 'Viernes', 'S' => 'Sábado', 'D' => 'Domingo'];
                                            $selected = isset($usage->use_days_of_week) ? explode(',', $usage->use_days_of_week) : [];
                                        @endphp
                                        @foreach($days as $key => $label)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input day-checkbox-{{ $equipment->id }}" type="checkbox" name="usages[{{ $equipment->id }}][use_days_of_week][]" value="{{ $key }}" id="day_{{ $equipment->id }}_{{ $key }}" {{ in_array($key, $selected) ? 'checked' : '' }} onchange="checkAllDaysStatus{{ $equipment->id }}()">
                                                <label class="form-check-label" for="day_{{ $equipment->id }}_{{ $key }}">{{ $key }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Selecciona los días de uso semanal. Ejemplo: L M X J V</small>
                                    <script>
                                        function toggleAllDays{{ $equipment->id }}(checked) {
                                            const checkboxes = document.querySelectorAll('.day-checkbox-{{ $equipment->id }}');
                                            checkboxes.forEach(cb => cb.checked = checked);
                                        }
                                        
                                        function checkAllDaysStatus{{ $equipment->id }}() {
                                            const checkboxes = document.querySelectorAll('.day-checkbox-{{ $equipment->id }}');
                                            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                                            document.getElementById('all_days_{{ $equipment->id }}').checked = allChecked;
                                        }
                                        
                                        // Check initial state
                                        checkAllDaysStatus{{ $equipment->id }}();
                                    </script>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @empty
            <div class="alert alert-info text-center">No hay equipos activos para ajustar en este periodo.</div>
        @endforelse
        <div class="mb-3">
            <label for="notes" class="form-label">Notas generales del ajuste</label>
            <textarea name="notes" id="notes" class="form-control">{{ $usageAdjustment->notes ?? '' }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar ajuste</button>
            @if(isset($equipmentUsage))
                <a href="{{ route('usage_adjustments.edit', [$invoice->id, $equipment->id]) }}" class="btn btn-secondary ms-2">Editar ajuste</a>
            @endif
        <a href="{{ route('usage_adjustments.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
@endsection
