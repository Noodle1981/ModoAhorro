@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart-line"></i> Panel de Consumo Energético - Detalle</h2>
        <a href="{{ route('consumption.panel') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow border-primary">
                <div class="card-body">
                    <h4 class="card-title mb-3"><i class="bi bi-speedometer2"></i> Análisis de Consumo</h4>
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-12 text-center">
                            <h6 class="text-muted text-uppercase">Consumo Facturado</h6>
                            <h2 class="display-6 fw-bold text-primary">{{ number_format($invoice->total_energy_consumed_kwh, 2) }} kWh</h2>
                        </div>
                    </div>

                    {{-- Alerta de Desviación --}}
                    @if(isset($validation) && $validation['alert_level'] === 'danger')
                        <div class="alert alert-danger mt-3">
                            <h5><i class="bi bi-exclamation-triangle-fill"></i> Desviación Alta Detectada</h5>
                            <p>El consumo calculado difiere en <strong>{{ $validation['deviation_percent'] }}%</strong> del facturado.</p>
                            
                            @if(count($suggestions) > 0)
                                <p class="mb-2"><strong>Sugerencias para corregir:</strong></p>
                                <ul class="mb-3">
                                    @foreach($suggestions as $suggestion)
                                        <li>{{ $suggestion }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="btn btn-warning">
                                <i class="bi bi-sliders"></i> Revisar Ajustes
                            </a>
                        </div>
                    @elseif(isset($validation) && $validation['alert_level'] === 'warning')
                        <div class="alert alert-warning mt-3">
                            <strong><i class="bi bi-exclamation-circle"></i> Desviación moderada:</strong> {{ $validation['deviation_percent'] }}%
                            <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="btn-link ms-2">
                                Revisar Ajustes
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Consumo por Categoría</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($consumoPorCategoria as $categoria => $consumo)
                            @php 
                                $pctCat = $totalEnergia > 0 ? ($consumo / $totalEnergia) * 100 : 0;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $categoria }}</strong>
                                    <div class="progress mt-1" style="height: 5px; width: 100px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $pctCat }}%"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">{{ number_format($consumo, 2) }} kWh</span>
                                    <br>
                                    <small class="text-muted">{{ number_format($pctCat, 1) }}%</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Detalles del Periodo</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted border-bottom pb-2 mb-3">Facturación</h6>
                    <div class="mb-3">
                        <p class="mb-1"><strong>Nº Factura:</strong> #{{ $invoice->id }}</p>
                        <p class="mb-1">
                            <strong>Periodo:</strong> 
                            @php
                                $startDate = \Carbon\Carbon::parse($invoice->start_date);
                                $endDate = \Carbon\Carbon::parse($invoice->end_date);
                                $days = $startDate->diffInDays($endDate);
                            @endphp
                            {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                            <span class="badge bg-light text-dark ms-1">{{ $days }} días</span>
                        </p>
                        <p class="mb-1"><strong>Monto Total:</strong> ${{ number_format($invoice->total_amount, 2) }}</p>
                        <p class="mb-0"><strong>Potencia Instalada:</strong> {{ number_format($totalPotencia, 0) }} W</p>
                    </div>

                    @if(isset($climateStats))
                        <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Clima (Histórico)</h6>
                        <div class="row text-center g-2">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <small class="d-block text-muted">Promedio</small>
                                    <span class="fw-bold">{{ $climateStats['avg_temp_avg'] ?? '-' }}°C</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-danger bg-opacity-10 rounded">
                                    <small class="d-block text-muted">Máxima</small>
                                    <span class="fw-bold text-danger">{{ $climateStats['avg_temp_max'] ?? '-' }}°C</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-primary bg-opacity-10 rounded">
                                    <small class="d-block text-muted">Mínima</small>
                                    <span class="fw-bold text-primary">{{ $climateStats['avg_temp_min'] ?? '-' }}°C</span>
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <small class="text-danger"><i class="bi bi-thermometer-sun"></i> Días Calor (>28°C): <strong>{{ $climateStats['hot_days_count'] ?? 0 }}</strong></small>
                            </div>
                            <div class="col-6 mt-2">
                                <small class="text-primary"><i class="bi bi-thermometer-snow"></i> Días Frío (<15°C): <strong>{{ $climateStats['cold_days_count'] ?? 0 }}</strong></small>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <h6 class="text-muted mb-3"><i class="bi bi-info-circle"></i> Guía de Ajustes</h6>
                        <ul class="list-unstyled small text-muted">
                            <li class="mb-2">
                                <span class="badge bg-success"><i class="bi bi-shield-lock"></i> Hormiga</span>
                                <strong>Protegido:</strong> Equipos de bajo consumo o uso fijo (Heladeras, Luces). Su cálculo se considera exacto y no se altera.
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning text-dark"><i class="bi bi-sliders"></i> Ballena</span>
                                <strong>Ajustado:</strong> Equipos de alta potencia variable. Absorben la diferencia para coincidir con la factura real.
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Ajuste Global</span>
                                <strong>Crítico:</strong> La factura es tan baja que no cubre ni siquiera los consumos "Hormiga". Se redujo todo proporcionalmente.
                            </li>
                            <li>
                                <span class="badge bg-info text-dark"><i class="bi bi-thermometer-sun"></i> Clima</span>
                                <strong>Ajuste Climático:</strong> El uso se recalculó basándose en los días reales de frío/calor registrados en la zona durante este periodo.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h5 class="mt-4 mb-3"><i class="bi bi-lightbulb"></i> Detalle por Equipo</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Equipo</th>
                            <th>Categoría</th>
                            <th>Habitación</th>
                            <th>Potencia (W)</th>
                            <th>Consumo (kWh)</th>
                            <th>Ajustado con API de Clima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->equipmentUsages as $usage)
                            <tr>
                                <td class="fw-bold">{{ $usage->equipment->name }}</td>
                                <td><span class="badge bg-secondary">{{ $usage->equipment->category->name ?? 'General' }}</span></td>
                                <td>{{ $usage->equipment->room->name ?? '-' }}</td>
                                <td>{{ $usage->equipment->nominal_power_w ?? '-' }}</td>
                                <td class="fw-bold text-primary">{{ number_format($consumos[$usage->equipment_id] ?? 0, 2) }}</td>
                            <td>
                                @php
                                    // Buscar el objeto calibratedUsage correspondiente
                                    $calibratedUsage = $calibratedUsages->firstWhere('equipment_id', $usage->equipment_id);
                                    $status = $calibratedUsage->calibration_status ?? null;
                                    $note = $calibratedUsage->calibration_note ?? '';
                                @endphp

                                @if($status === 'BASE_CRITICAL')
                                    <span class="badge bg-success" title="{{ $note }}">
                                        <i class="bi bi-shield-lock-fill"></i> Base Crítica
                                    </span>
                                @elseif($status === 'BASE_HEAVY')
                                    <span class="badge bg-success bg-opacity-75" title="{{ $note }}">
                                        <i class="bi bi-droplet-fill"></i> Base Pesada
                                    </span>
                                @elseif($status === 'PROTECTED_ANT')
                                    <span class="badge bg-success bg-opacity-50 text-dark" title="{{ $note }}">
                                        <i class="bi bi-shield-lock"></i> Hormiga
                                    </span>
                                @elseif($status === 'WEIGHTED_ADJUSTMENT')
                                    @if(($usage->equipment->category->name ?? '') === 'Climatización')
                                        <span class="badge bg-warning text-dark" title="{{ $note }}">
                                            <i class="bi bi-sliders"></i> Ballena (Clima Ajustado)
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark" title="{{ $note }}">
                                            <i class="bi bi-sliders"></i> Ballena (Ajustado)
                                        </span>
                                    @endif
                                @elseif($status === 'CRITICAL_CUT')
                                    <span class="badge bg-danger" title="{{ $note }}">
                                        <i class="bi bi-exclamation-octagon"></i> Recorte Crítico
                                    </span>
                                @elseif($status === 'HEAVY_CUT')
                                    <span class="badge bg-danger bg-opacity-75" title="{{ $note }}">
                                        <i class="bi bi-scissors"></i> Recorte Pesado
                                    </span>
                                @elseif($status === 'ANT_CUT')
                                    <span class="badge bg-danger bg-opacity-50" title="{{ $note }}">
                                        <i class="bi bi-scissors"></i> Recorte Hormiga
                                    </span>
                                @elseif($status === 'ZERO_ALLOCATION')
                                    <span class="badge bg-secondary" title="{{ $note }}">
                                        <i class="bi bi-slash-circle"></i> Apagado Forzoso
                                    </span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 mb-4 shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Guía de Ajustes (Motor Integral)</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <h6 class="text-success"><i class="bi bi-shield-lock-fill"></i> Nivel 1: Base Crítica</h6>
                <small class="text-muted d-block mb-2">Intocables. Se llenan primero.</small>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-check-circle-fill text-success"></i> Heladeras</li>
                    <li><i class="bi bi-check-circle-fill text-success"></i> Routers / Alarmas</li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-success text-opacity-75"><i class="bi bi-droplet-fill"></i> Nivel 2: Base Pesada</h6>
                <small class="text-muted d-block mb-2">Confort básico. Se llenan segundo.</small>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-check-circle text-success"></i> Termotanques</li>
                    <li><i class="bi bi-check-circle text-success"></i> Bombas de Agua</li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-success text-opacity-50"><i class="bi bi-shield-check"></i> Nivel 3: Hormigas</h6>
                <small class="text-muted d-block mb-2">Infraestructura. Se llenan tercero.</small>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-lightbulb text-success"></i> Iluminación</li>
                    <li><i class="bi bi-battery-charging text-success"></i> Cargadores</li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-warning"><i class="bi bi-sliders"></i> Nivel 4: Ballenas</h6>
                <small class="text-muted d-block mb-2">Ocio y Clima. Absorben variabilidad.</small>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-snow text-warning"></i> Aires / Estufas</li>
                    <li><i class="bi bi-pc-display text-warning"></i> PC Gamer / TV</li>
                </ul>
            </div>
        </div>
        <div class="row mt-3 pt-3 border-top">
            <div class="col-12">
                <small class="text-muted"><i class="bi bi-info-circle"></i> <strong>Nota:</strong> Los termotanques incluyen ajuste climático automático (x1.25 en invierno, x0.85 en verano).</small>
            </div>
        </div>
    </div>
</div>
