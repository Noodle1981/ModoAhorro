@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-danger text-white">
                    <h4><i class="bi bi-fire"></i> Calculadora de Calefones Solares</h4>
                    <p class="mb-0 small">Estima el ahorro en gas/electricidad calentando agua con el sol</p>
                </div>
                <div class="card-body">
                    <h5>Propiedad: {{ $entity->name }}</h5>
                    <p class="text-muted">
                        <i class="bi bi-geo-alt"></i> {{ $entity->address_street }}, {{ $entity->locality->name ?? 'N/A' }}
                    </p>
                    
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Recomendación</h5>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4 text-success">{{ $waterHeaterData['recommended_equipment_liters'] }} Litros</h2>
                                    <p class="lead">Equipo Termotanque Solar</p>
                                    <hr>
                                    <div class="d-flex justify-content-around">
                                        <div>
                                            <strong>{{ $waterHeaterData['people_count'] }}</strong><br>
                                            <small class="text-muted">Personas</small>
                                        </div>
                                        <div>
                                            <strong>{{ number_format($waterHeaterData['daily_liters'], 0) }} L</strong><br>
                                            <small class="text-muted">Demanda Diaria</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-piggy-bank"></i> Ahorro Estimado</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="fuelTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="natural-gas-tab" data-bs-toggle="tab" data-bs-target="#natural-gas" type="button" role="tab" aria-selected="true">Gas Natural</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas" type="button" role="tab" aria-selected="false">Gas (Garrafa)</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="electric-tab" data-bs-toggle="tab" data-bs-target="#electric" type="button" role="tab" aria-selected="false">Electricidad</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content pt-3" id="fuelTabContent">
                                        <div class="tab-pane fade show active" id="natural-gas" role="tabpanel">
                                            <h3 class="text-primary mb-0">${{ number_format($waterHeaterData['savings']['gas_natural']['monthly_savings'], 0, ',', '.') }}</h3>
                                            <small class="text-muted">Ahorro Mensual Promedio</small>
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between border-bottom pb-2">
                                                    <span>Consumo evitado:</span>
                                                    <strong>{{ $waterHeaterData['savings']['gas_natural']['m3_per_month'] }} m³/mes</strong>
                                                </div>
                                                <div class="d-flex justify-content-between pt-2">
                                                    <span>Ahorro Anual:</span>
                                                    <strong class="text-success">${{ number_format($waterHeaterData['savings']['gas_natural']['annual_savings'], 0, ',', '.') }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="gas" role="tabpanel">
                                            <h3 class="text-primary mb-0">${{ number_format($waterHeaterData['savings']['gas']['monthly_savings'], 0, ',', '.') }}</h3>
                                            <small class="text-muted">Ahorro Mensual Promedio</small>
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between border-bottom pb-2">
                                                    <span>Garrafas evitadas/mes:</span>
                                                    <strong>{{ $waterHeaterData['savings']['gas']['garrafas_per_month'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between pt-2">
                                                    <span>Ahorro Anual:</span>
                                                    <strong class="text-success">${{ number_format($waterHeaterData['savings']['gas']['annual_savings'], 0, ',', '.') }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="electric" role="tabpanel">
                                            <h3 class="text-primary mb-0">${{ number_format($waterHeaterData['savings']['electric']['monthly_savings'], 0, ',', '.') }}</h3>
                                            <small class="text-muted">Ahorro Mensual Promedio</small>
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between border-bottom pb-2">
                                                    <span>Energía ahorrada:</span>
                                                    <strong>{{ number_format($waterHeaterData['monthly_energy_kwh'] * 0.75, 0) }} kWh/mes</strong>
                                                </div>
                                                <div class="d-flex justify-content-between pt-2">
                                                    <span>Ahorro Anual:</span>
                                                    <strong class="text-success">${{ number_format($waterHeaterData['savings']['electric']['annual_savings'], 0, ',', '.') }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            <strong>¿Sabías que?</strong>
                            El sol puede cubrir el 100% de tu necesidad de agua caliente en verano y hasta el 60% en invierno.
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('entities.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
