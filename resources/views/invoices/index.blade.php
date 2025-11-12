@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Facturas del Hogar</h1>
    @if(!$contract)
        <div class="alert alert-warning">No hay contrato activo registrado para este hogar. No es posible cargar facturas.</div>
    @else
        <a href="{{ route('entities.invoices.create', $entity->id) }}" class="btn btn-success mb-3">Cargar Nueva Factura</a>
        @if(empty($invoices) || $invoices->isEmpty())
            <div class="alert alert-info">No hay facturas registradas.</div>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Importe</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->id }}</td>
                            <td>{{ $invoice->start_date ?? '-' }}</td>
                            <td>{{ $invoice->end_date ?? '-' }}</td>
                            <td>${{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                            <td>
                                <a href="{{ route('entities.invoices.edit', [$entity->id, $invoice->id]) }}" class="btn btn-primary btn-sm">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</div>
@endsection
