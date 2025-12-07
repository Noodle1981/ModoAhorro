@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-sync-alt text-success mr-2"></i>Catálogo de Reemplazos
            </h1>
            <p class="mb-0 text-muted">Oportunidades de ahorro detectadas para {{ $entity->name }}</p>
        </div>
        <div>
            <a href="{{ route('efficiency-benchmarks.index') }}" class="btn btn-outline-primary mr-2">
                <i class="fas fa-cog mr-1"></i> Configurar Benchmarks
            </a>
            <a href="{{ route('entities.show', $entity) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    @if(count($opportunities) > 0)
        <div class="row">
            @foreach($opportunities as $op)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card border-left-{{ $op['verdict']['color'] }} shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-{{ $op['verdict']['color'] }} text-uppercase mb-1">
                                        {{ $op['verdict']['label'] }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $op['equipment_name'] }}
                                    </div>
                                    <div class="mt-2 small text-muted">
                                        Consumo actual: <strong>{{ $op['current_consumption_kwh'] }} kWh</strong>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-plug fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="row text-center mb-3">
                                <div class="col-6 border-right">
                                    <div class="small text-gray-500">Ahorro Mensual</div>
                                    <div class="h6 font-weight-bold text-success">
                                        ${{ number_format($op['monthly_savings_amount'], 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-gray-500">Recupero</div>
                                    <div class="h6 font-weight-bold">
                                        {{ $op['payback_months'] }} meses
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border small mb-3">
                                <i class="fas fa-lightbulb text-warning mr-1"></i>
                                Sugerencia: <strong>{{ $op['replacement_suggestion'] }}</strong>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('replacements.refine', $op['equipment_id']) }}" class="btn btn-outline-secondary btn-block mt-0">
                                    <i class="fas fa-edit mr-1"></i> Editar Datos
                                </a>
                                <a href="{{ $op['affiliate_link'] ?? '#' }}" target="_blank" class="btn btn-{{ $op['verdict']['color'] }} btn-block mt-0">
                                    <i class="fas fa-shopping-cart mr-1"></i> Ver en Mercado Libre
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info shadow-sm mb-4">
            <h4 class="alert-heading"><i class="fas fa-check-circle mr-2"></i>¡Todo parece optimizado!</h4>
            <p class="mb-0">No detectamos oportunidades obvias de reemplazo con la información actual.</p>
        </div>
    @endif

    @if(isset($analyzableEquipments) && count($analyzableEquipments) > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Mis Equipos Analizados</h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Si crees que alguno de estos equipos es ineficiente, agrega más detalles (Año, Etiqueta) para recalcular.</p>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Categoría</th>
                                <th>Detalles Actuales</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analyzableEquipments as $eq)
                                <tr>
                                    <form action="{{ route('replacements.update_refinement', $eq->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <td class="align-middle">
                                            <strong>{{ $eq->name }}</strong>
                                        </td>
                                        <td class="align-middle">{{ $eq->category->name ?? '-' }}</td>
                                        <td>
                                            <div class="form-row align-items-center">
                                                <div class="col-auto">
                                                    <input type="number" name="acquisition_year" class="form-control form-control-sm" placeholder="Año (Ej: 2015)" value="{{ $eq->acquisition_year }}" style="width: 100px;">
                                                </div>
                                                <div class="col-auto">
                                                    <select name="energy_label" class="form-control form-control-sm">
                                                        <option value="">Etiqueta...</option>
                                                        @foreach(['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E'] as $label)
                                                            <option value="{{ $label }}" {{ $eq->energy_label == $label ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="inverter_{{ $eq->id }}" name="is_inverter" value="1" {{ $eq->is_inverter ? 'checked' : '' }}>
                                                        <label class="custom-control-label small" for="inverter_{{ $eq->id }}">Inverter</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-save"></i> Guardar
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
