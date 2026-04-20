@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-2 text-gray-800">Dashboard de Auditoría - Motor Energético v3</h1>
            <p class="mb-4">Supervisión detallada de cómo el motor procesó cada factura y asignó el consumo a los tanques.</p>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Facturas Procesadas (Últimas 10)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Entidad / Hogar</th>
                            <th>Periodo</th>
                            <th>Consumo (kWh)</th>
                            <th>Equipos Auditados</th>
                            <th>Fecha Proceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->id }}</td>
                            <td>
                                <strong>{{ $invoice->contract->entity->name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $invoice->contract->supply_number ?? 'S/N' }}</small>
                            </td>
                            <td>{{ $invoice->start_date }} <br>al<br> {{ $invoice->end_date }}</td>
                            <td>{{ $invoice->total_consumption_kwh }} kWh</td>
                            <td>{{ $invoice->equipmentUsages->whereNotNull('audit_logs')->count() }}</td>
                            <td>{{ $invoice->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm" type="button" data-toggle="collapse" data-target="#auditDetails{{ $invoice->id }}" aria-expanded="false">
                                    <i class="fas fa-eye"></i> Ver Logs
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <div class="collapse" id="auditDetails{{ $invoice->id }}">
                                    <div class="card card-body bg-light m-3">
                                        <h5 class="text-dark">Detalle de Auditoría por Equipo</h5>
                                        <div class="row">
                                            @foreach($invoice->equipmentUsages->whereNotNull('audit_logs') as $usage)
                                            <div class="col-md-6 mb-3">
                                                <div class="card h-100 border-left-info">
                                                    <div class="card-body">
                                                        <h6 class="font-weight-bold text-primary">{{ $usage->equipment->name ?? 'Equipo Eliminado' }}</h6>
                                                        <div class="small text-muted mb-2">
                                                            Consumo Reconciliado: <strong>{{ number_format($usage->kwh_reconciled, 2) }} kWh</strong><br>
                                                            Tanque Asignado: <strong>{{ $usage->tank_assignment ?? 'N/A' }}</strong>
                                                        </div>
                                                        <div class="p-2 bg-dark text-white rounded small" style="max-height: 150px; overflow-y: auto; font-family: monospace;">
                                                            @if(is_array($usage->audit_logs))
                                                                <ul class="list-unstyled mb-0">
                                                                @foreach($usage->audit_logs as $log)
                                                                    <li>> {{ $log }}</li>
                                                                @endforeach
                                                                </ul>
                                                            @else
                                                                {{ $usage->audit_logs }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay facturas auditadas con el Motor v3 aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
