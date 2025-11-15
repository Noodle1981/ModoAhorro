@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ajuste de uso por factura</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Factura</th>
                <th>Periodo</th>
                <th>Estado de ajuste</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}</td>
                    <td>
                        @if($invoice->usageAdjustment && $invoice->usageAdjustment->adjusted)
                            <span class="badge bg-success">Ajustado</span>
                        @else
                            <span class="badge bg-warning text-dark">Necesita ajustar</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="btn btn-primary btn-sm">Editar ajuste</a>
                        <a href="{{ route('usage_adjustments.show', $invoice->id) }}" class="btn btn-outline-info btn-sm ms-1">Ver detalle</a>
                    </td
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-3">
        <a href="{{ route('entities.index') }}" class="btn btn-secondary">
            <i class="bi bi-house-door"></i> Ir al panel de entidades
        </a>
    </div>
</div>
@endsection
