@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ajuste de uso - Factura #{{ $invoice->id }}</h2>
    <form method="POST" action="{{ route('usage_adjustments.update', $invoice->id) }}">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Equipo</th>
                    <th>Habitación</th>
                    <th>Potencia (W)</th>
                    <!-- <th>¿Standby?</th> -->
                    <th>Horas uso diario (promedio)</th>
                    <th>Días de uso en el periodo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipments as $equipment)
                    @php $usage = $usages[$equipment->id] ?? null; @endphp
                    <tr>
                        <td>{{ $equipment->name }}</td>
                        <td>{{ $equipment->room->name ?? '-' }}</td>
                        <td>{{ $equipment->nominal_power_w ?? '-' }}</td>
                            <td>
                                <a href="{{ route('usage_adjustments.edit', [$invoice->id, $equipment->id]) }}" class="btn btn-sm btn-warning">Editar</a>
                            </td>
                        <!-- <td>
                            <input type="checkbox" name="usages[{{ $equipment->id }}][is_standby]" value="1" {{ ($usage && $usage->is_standby) ? 'checked' : '' }}>
                        </td> -->
                        <td>
                            <input type="range" min="0" max="24" step="0.1" name="usages[{{ $equipment->id }}][avg_daily_use_hours]" value="{{ $usage->avg_daily_use_hours ?? 0 }}" class="form-range" oninput="updateHours{{ $equipment->id }}(this.value)">
                            <input type="number" min="0" max="24" step="0.1" id="hours_{{ $equipment->id }}" name="usages[{{ $equipment->id }}][avg_daily_use_hours]" value="{{ $usage->avg_daily_use_hours ?? 0 }}" class="form-control d-inline-block w-auto ms-2" style="width:80px;" oninput="updateHours{{ $equipment->id }}(this.value)">
                            <span id="minutes_{{ $equipment->id }}" class="ms-2 text-info">{{ isset($usage->avg_daily_use_hours) ? round($usage->avg_daily_use_hours * 60) : 0 }} min</span>
                            <small class="text-muted d-block">Selecciona las horas promedio de uso diario (0 si no se usó en el periodo). El valor se muestra también en minutos.</small>
                            <script>
                                function updateHours{{ $equipment->id }}(val) {
                                    document.getElementById('hours_{{ $equipment->id }}').value = val;
                                    document.getElementById('minutes_{{ $equipment->id }}').innerText = Math.round(val * 60) + ' min';
                                }
                            </script>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @php
                                    $days = ['L' => 'Lunes', 'M' => 'Martes', 'X' => 'Miércoles', 'J' => 'Jueves', 'V' => 'Viernes', 'S' => 'Sábado', 'D' => 'Domingo'];
                                    $selected = isset($usage->use_days_of_week) ? explode(',', $usage->use_days_of_week) : [];
                                @endphp
                                @foreach($days as $key => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="usages[{{ $equipment->id }}][use_days_of_week][]" value="{{ $key }}" id="day_{{ $equipment->id }}_{{ $key }}" {{ in_array($key, $selected) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day_{{ $equipment->id }}_{{ $key }}">{{ $key }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Selecciona los días de uso semanal. Ejemplo: L M X J V</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay equipos activos para ajustar en este periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
