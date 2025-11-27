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
                                        <small class="text-muted">Basado en factura #{{ $invoiceData['number'] ?? 'N/A' }}</small>
                                    @else
                                        <h3 class="mb-0 text-muted">-- kWh</h3>
                                        <small class="text-muted">Sin datos de facturación</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($invoiceData)
                    <div class="alert alert-light border">
                        <h6><i class="bi bi-receipt"></i> Última Factura</h6>
                        <div class="row small">
                            <div class="col-md-3">
                                <strong>Nº:</strong> #{{ $invoiceData['number'] }}
                            </div>
                            <div class="col-md-4">
                                <strong>Periodo:</strong> {{ \Carbon\Carbon::parse($invoiceData['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($invoiceData['end_date'])->format('d/m/Y') }}
                            </div>
                            <div class="col-md-2">
                                <strong>{{ $invoiceData['period_days'] }}</strong> días
                            </div>
                            <div class="col-md-3">
                                <strong>Total:</strong> ${{ number_format($invoiceData['total_amount'], 2) }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="mb-4">
                        <label for="roofPercentage" class="form-label">
                            <strong>¿Qué porcentaje de tu techo está disponible para paneles solares?</strong>
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" class="form-range flex-grow-1" id="roofPercentage" 
                                   min="0" max="100" value="50" step="5">
                            <span class="badge bg-warning text-dark fs-5" id="percentageDisplay">50%</span>
                        </div>
                        <small class="text-muted">Mueve el control para ajustar el área disponible</small>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Estimación de Instalación Solar</h6>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Área disponible:</span>
                                    <strong id="availableArea">225 m²</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Capacidad estimada:</span>
                                    <strong id="systemCapacity">38.3 kWp</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Generación mensual:</span>
                                    <strong class="text-success" id="monthlyGeneration">5,130 kWh</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Generación anual:</span>
                                    <strong class="text-success" id="annualGeneration">61,560 kWh</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="bi bi-cash-coin"></i> Ahorro Estimado</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span>Ahorro mensual:</span>
                                    <strong id="monthlySavings">$--</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span>Ahorro anual:</span>
                                    <strong id="annualSavings">$--</strong>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            * Cálculo estimado basado en tarifa promedio de $150/kWh
                        </small>
                    </div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalArea = {{ $entity->square_meters }};
    const slider = document.getElementById('roofPercentage');
    const percentageDisplay = document.getElementById('percentageDisplay');
    const availableAreaEl = document.getElementById('availableArea');
    const systemCapacityEl = document.getElementById('systemCapacity');
    const monthlyGenerationEl = document.getElementById('monthlyGeneration');
    const annualGenerationEl = document.getElementById('annualGeneration');
    const monthlySavingsEl = document.getElementById('monthlySavings');
    const annualSavingsEl = document.getElementById('annualSavings');
    
    // Modal elements
    const modalArea = document.getElementById('modalArea');
    const modalCapacity = document.getElementById('modalCapacity');
    
    // Constants for calculations
    const PANEL_EFFICIENCY = 0.17; // 170W per m²
    const PEAK_SUN_HOURS = 4.5; // hours per day in San Juan
    const DAYS_PER_MONTH = 30;
    const TARIFF_PER_KWH = 150; // pesos per kWh
    
    function calculate() {
        const percentage = parseInt(slider.value);
        const availableArea = (totalArea * percentage) / 100;
        const systemCapacity = availableArea * PANEL_EFFICIENCY; // kWp
        const dailyGeneration = systemCapacity * PEAK_SUN_HOURS; // kWh/day
        const monthlyGeneration = dailyGeneration * DAYS_PER_MONTH; // kWh/month
        const annualGeneration = monthlyGeneration * 12; // kWh/year
        
        const monthlySavings = monthlyGeneration * TARIFF_PER_KWH;
        const annualSavings = annualGeneration * TARIFF_PER_KWH;
        
        // Update display
        percentageDisplay.textContent = percentage + '%';
        availableAreaEl.textContent = availableArea.toFixed(1) + ' m²';
        systemCapacityEl.textContent = systemCapacity.toFixed(1) + ' kWp';
        monthlyGenerationEl.textContent = monthlyGeneration.toLocaleString('es-AR', {maximumFractionDigits: 0}) + ' kWh';
        annualGenerationEl.textContent = annualGeneration.toLocaleString('es-AR', {maximumFractionDigits: 0}) + ' kWh';
        monthlySavingsEl.textContent = '$' + monthlySavings.toLocaleString('es-AR', {maximumFractionDigits: 0});
        annualSavingsEl.textContent = '$' + annualSavings.toLocaleString('es-AR', {maximumFractionDigits: 0});
        
        // Update modal
        modalArea.textContent = availableArea.toFixed(1);
        modalCapacity.textContent = systemCapacity.toFixed(1);
    }
    
    slider.addEventListener('input', calculate);
    
    // Initial calculation
    calculate();
});
</script>
@endsection
