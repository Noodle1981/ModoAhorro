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
                    <td>{{ $invoice->start_date }} - {{ $invoice->end_date }}</td>
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
</div>
@endsection
