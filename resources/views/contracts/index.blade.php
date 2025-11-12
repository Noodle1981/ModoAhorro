@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Contratos</h1>
    <a href="{{ route('contracts.create') }}" class="btn btn-success mb-3">Nuevo Contrato</a>
    <a href="{{ route('entities.index') }}" class="btn btn-secondary mb-3 ms-2">Volver a entidad</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Entidad</th>
                <th>N° de Suministro</th>
                <th>Empresa Distribuidora</th>
                <th>N° Serie Medidor</th>
                <th>Identificador</th>
                <th>Tarifa</th>
                <th>Potencia P1</th>
                <th>Potencia P2</th>
                <th>Potencia P3</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contracts as $contract)
                <tr>
                    <td>{{ $contract->id }}</td>
                    <td>{{ $contract->entity->name ?? '-' }}</td>
                    <td>{{ $contract->supply_number }}</td>
                    <td>{{ $contract->proveedor->name ?? '-' }}</td>
                    <td>{{ $contract->serial_number }}</td>
                    <td>{{ $contract->contract_identifier }}</td>
                    <td>{{ $contract->rate_name }}</td>
                    <td>{{ $contract->contracted_power_kw_p1 }}</td>
                    <td>{{ $contract->contracted_power_kw_p2 }}</td>
                    <td>{{ $contract->contracted_power_kw_p3 }}</td>
                    <td>{{ $contract->start_date }}</td>
                    <td>{{ $contract->end_date }}</td>
                    <td>{{ $contract->is_active ? 'Sí' : 'No' }}</td>
                    <td>
                        <a href="{{ route('contracts.edit', $contract->id) }}" class="btn btn-primary btn-sm">Editar</a>
                        <form method="POST" action="{{ route('contracts.destroy', $contract->id) }}" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este contrato?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
