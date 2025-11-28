@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <h4><i class="bi bi-sun-fill"></i> Calculadora de Paneles Solares</h4>
                    <p class="mb-0 small">Estima el potencial de energía solar para tu propiedad</p>
                </div>
                <div class="card-body">
                    <h5>Propiedad: {{ $entity->name }}</h5>
                    <p class="text-muted">
                        <i class="bi bi-geo-alt"></i> {{ $entity->address_street }}, {{ $entity->locality->name ?? 'N/A' }}
                    </p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">Superficie Total</h6>
                                    <h3 class="mb-0">{{ $entity->square_meters }} m²</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted">Consumo Mensual Promedio</h6>
                                    @if($monthlyConsumption)
                                        <h3 class="mb-0">{{ number_format($monthlyConsumption, 0) }} kWh</h3>
                                        <small class="text-muted">Promedio basado en {{ $invoiceCount }} factura(s)</small>
                                    @else
                                        <h3 class="mb-0 text-muted">-- kWh</h3>
                                        <small class="text-muted">Sin datos de facturación</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($invoices->isNotEmpty())
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-receipt"></i> Historial de Facturas</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nº Factura</th>
                                        <th>Periodo</th>
                                        <th class="text-center">Días</th>
                                        <th class="text-end">Consumo</th>
                                        <th class="text-end">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        @php
                                            $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
                                            $startDate = \Carbon\Carbon::parse($invoice->start_date);
                                            $endDate = \Carbon\Carbon::parse($invoice->end_date);
                                            $days = $startDate->diffInDays($endDate);
                                        @endphp
                                        <tr>
                                            <td>#{{ $invoice->invoice_number }}</td>
                                            <td>{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ $days }}</td>
                                            <td class="text-end">{{ number_format($consumption, 0) }} kWh</td>
                                            <td class="text-end">${{ number_format($invoice->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <hr>



                    @if(isset($climateProfile) && !empty($climateProfile))
                    <div class="card mb-4 border-warning shadow-sm">
                        <div class="card-header bg-warning text-dark bg-opacity-10">
                            <h6 class="mb-0"><i class="bi bi-sun"></i> Perfil Solar de tu Zona</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4 border-end">
                                    <h4 class="mb-0 text-warning">{{ $climateProfile['avg_radiation'] }}</h4>
                                    <small class="text-muted">Radiación (MJ/m²)</small>
                                </div>
                                <div class="col-4 border-end">
                                    <h4 class="mb-0 text-warning">{{ $climateProfile['avg_sunshine_duration'] }}</h4>
                                    <small class="text-muted">Horas de Sol</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="mb-0 text-secondary">{{ $climateProfile['avg_cloud_cover'] }}%</h4>
                                    <small class="text-muted">Nubosidad</small>
                                </div>
                            </div>
                            <div class="mt-3 small text-muted text-center">
                                <i class="bi bi-info-circle"></i> Datos históricos reales de {{ $entity->locality->name ?? 'tu zona' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($solarData))
                    <div class="card mb-4 border-success shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-battery-charging"></i> Cobertura Solar Estimada</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert {{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'alert-success' : 'alert-warning' }} mb-3">
                                <i class="bi {{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                                <strong>{{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'Cobertura Total Posible' : 'Cobertura Parcial (Limitada por Espacio)' }}</strong>
                            </div>

                            <div class="row text-center mb-4">
                                <div class="col-md-4">
                                    <h3 class="text-success">{{ $solarData['system_size_kwp'] }} kWp</h3>
                                    <small class="text-muted">Potencia a Instalar</small>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-primary">{{ $solarData['panels_count'] }}</h3>
                                    <small class="text-muted">Paneles (550W)</small>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-info">{{ number_format($solarData['area_used'], 1) }} m²</h3>
                                    <small class="text-muted">Espacio Requerido</small>
                                </div>
                            </div>

                            <div class="row mb-4 small text-muted text-center">
                                <div class="col-6 border-end">
                                    <span>Área Declarada: <strong>{{ $entity->square_meters }} m²</strong></span>
                                </div>
                                <div class="col-6">
                                    <span>Área Necesaria (100%): <strong>{{ number_format($solarData['target_area'], 1) }} m²</strong></span>
                                </div>
                            </div>

                            <h6 class="border-bottom pb-2 mb-3">Impacto en tu Consumo</h6>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cobertura Verano (Pico)</span>
                                    <span class="fw-bold">{{ $solarData['coverage_summer'] }}% {{ $solarData['coverage_summer'] < 100 ? '(Reduces tu factura)' : '(Cubres el pico)' }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $solarData['coverage_summer'] }}%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cobertura Invierno (Promedio)</span>
                                    <span class="fw-bold">{{ $solarData['coverage_winter'] }}% {{ $solarData['coverage_winter'] >= 100 ? '(Te sobra energía)' : '' }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $solarData['coverage_winter'] }}%"></div>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-lightning-fill text-warning"></i> Generación Mensual Est.: {{ $solarData['monthly_generation_kwh'] }} kWh
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="bi bi-cash-coin"></i> Ahorro Estimado</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span>Ahorro mensual (Promedio):</span>
                                    <strong>${{ number_format($estimatedMonthlySavings, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span>Ahorro anual:</span>
                                    <strong>${{ number_format($estimatedAnnualSavings, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            * Cálculo basado en simulación histórica con tarifa de ${{ number_format($averageTariff, 2) }}/kWh
                        </small>
                    </div>
                    @endif

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="bi bi-lightbulb"></i> Sobre esta estimación</h6>
                        <ul class="small mb-0">
                            <li>Eficiencia de paneles: ~170W por m²</li>
                            <li>Horas pico de sol en San Juan: ~4.5 horas/día</li>
                            <li>Los valores son aproximados y pueden variar según orientación del techo, sombras, y otros factores</li>
                        </ul>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('entities.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="button" class="btn btn-warning flex-grow-1" data-bs-toggle="modal" data-bs-target="#quoteModal">
                            <i class="bi bi-envelope"></i> Solicitar Presupuesto Personalizado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para solicitud de presupuesto -->
<div class="modal fade" id="quoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-envelope"></i> Solicitar Presupuesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¡Gracias por tu interés en energía solar!</p>
                <p>Hemos registrado tu consulta con los siguientes datos:</p>
                <ul>
                    <li><strong>Propiedad:</strong> {{ $entity->name }}</li>
                    <li><strong>Superficie:</strong> {{ $entity->square_meters }} m²</li>
                    <li><strong>Área disponible:</strong> <span id="modalArea">--</span> m²</li>
                    <li><strong>Capacidad estimada:</strong> <span id="modalCapacity">--</span> kWp</li>
                </ul>
                <p class="text-success"><i class="bi bi-check-circle"></i> Un especialista se pondrá en contacto contigo pronto.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


@endsection
