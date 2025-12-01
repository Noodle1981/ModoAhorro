@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-power"></i> Análisis de Consumo Fantasma</h2>
            <p class="text-muted mb-0">Detecta y gestiona el consumo de equipos en modo espera (Stand By) para {{ $entity->name }}</p>
        </div>
        <a href="{{ route('entities.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-secondary text-white h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightning-fill"></i> Consumo Actual</h5>
                    <h2 class="display-6">{{ number_format($totalStandbyKwh, 2) }} kWh</h2>
                    <p class="card-text">Consumo mensual estimado por Stand By.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-secondary"><i class="bi bi-piggy-bank"></i> Costo Estimado</h5>
                    <h2 class="display-6 text-secondary">${{ number_format($totalStandbyCost, 0, ',', '.') }}</h2>
                    <p class="card-text">Costo mensual adicional en tu factura.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-check-circle"></i> Ahorro Actual</h5>
                    @if($totalRealizedSavings > 0)
                        <h2 class="display-6 text-success">${{ number_format($totalRealizedSavings, 0, ',', '.') }}</h2>
                        <p class="card-text">
                            ¡Genial! Ya estás ahorrando <strong>${{ number_format($totalRealizedSavings, 0, ',', '.') }}</strong> este mes.
                        </p>
                    @else
                        <p class="card-text">
                            Desenchufando estos equipos cuando no los usas podrías ahorrar hasta 
                            <strong>${{ number_format($totalPotentialSavings, 0, ',', '.') }}</strong> al mes.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Equipos con Consumo Fantasma Detectado</h5>
        </div>
        <div class="card-body">
            @if($equipmentList->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-check-circle text-success display-4"></i>
                    <p class="mt-3 text-muted">No se detectaron equipos con consumo fantasma significativo.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Equipo</th>
                                <th>Ubicación</th>
                                <th>Potencia Stand By</th>
                                <th>Horas en Espera</th>
                                <th>Consumo Mensual</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipmentList as $eq)
                                @php
                                    $standbyPowerKw = ($eq->type->default_standby_power_w ?? 0) / 1000;
                                    // Calculate potential standby hours (24 - active use)
                                    $potentialStandbyHours = max(0, 24 - ($eq->avg_daily_use_hours ?? 0));
                                    
                                    // Calculate monthly cost if plugged in
                                    $monthlyKwh = $standbyPowerKw * $potentialStandbyHours * 30;
                                    $monthlyCost = $monthlyKwh * 150; // Using approx tariff
                                @endphp
                                <tr class="{{ $eq->is_standby ? '' : 'text-muted bg-light' }}">
                                    <td>
                                        <div class="fw-bold">{{ $eq->name }}</div>
                                        <small class="text-muted">{{ $eq->type->name ?? 'Desconocido' }}</small>
                                    </td>
                                    <td>{{ $eq->room->name ?? '-' }}</td>
                                    <td>{{ $eq->type->default_standby_power_w ?? 0 }} W</td>
                                    <td>
                                        {{ number_format($potentialStandbyHours, 1) }} hs/día
                                    </td>
                                    <td>
                                        @if($eq->is_standby)
                                            <span class="text-dark fw-bold">{{ number_format($monthlyKwh, 2) }} kWh</span>
                                            <br>
                                            <small class="text-danger">${{ number_format($monthlyCost, 0, ',', '.') }}</small>
                                        @else
                                            <span class="text-decoration-line-through text-muted">{{ number_format($monthlyKwh, 2) }} kWh</span>
                                            <br>
                                            <small class="text-success">Ahorrado</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($eq->is_standby)
                                            <span class="badge bg-warning text-dark"><i class="bi bi-plug-fill"></i> Enchufado</span>
                                        @else
                                            <span class="badge bg-success"><i class="bi bi-plug"></i> Desenchufado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('entities.standby.toggle', ['entity' => $entity->id, 'equipment' => $eq->id]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="switch{{ $eq->id }}" 
                                                    {{ $eq->is_standby ? 'checked' : '' }} 
                                                    onchange="this.form.submit()" style="cursor: pointer; transform: scale(1.3);">
                                                <label class="form-check-label" for="switch{{ $eq->id }}">
                                                    {{ $eq->is_standby ? 'ON' : 'OFF' }}
                                                </label>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
