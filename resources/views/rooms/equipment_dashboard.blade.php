@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Habitación: {{ $room->name }}</h1>
    <a href="{{ route('rooms.index', $room->entity_id) }}" class="btn btn-secondary mb-3">Volver a habitaciones</a>
    <div class="card mb-4">
        <div class="card-header">Equipos en esta habitación</div>
        <div class="card-body">
            @if($room->equipment->count())
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th>Potencia (W)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($room->equipment as $equipment)
                        <tr>
                            <td>{{ $equipment->name }}</td>
                            <td>{{ $equipment->category->name ?? '-' }}</td>
                            <td>{{ $equipment->type->name ?? '-' }}</td>
                            <td>{{ $equipment->nominal_power_w }}</td>
                            <td>
                                <a href="{{ route('rooms.equipment.edit', [$room->entity_id, $room->id, $equipment->id]) }}" class="btn btn-sm btn-primary">Editar</a>
                                <form action="{{ route('rooms.equipment.destroy', [$room->entity_id, $room->id, $equipment->id]) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este equipo? Si no es una mala carga, debe ir al centro de bajas/reemplazos.')">Eliminar</button>
                                </form>
                                <a href="#" class="btn btn-sm btn-secondary disabled" title="Centro de bajas/reemplazos">Centro de bajas</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No hay equipos cargados en esta habitación.</p>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header">Agregar nuevo equipo</div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle-fill"></i> <strong>Nota importante:</strong> Los valores de potencia sugeridos son estimaciones promedio. Para obtener un cálculo preciso, por favor verifique el consumo real en el manual de su equipo o en la etiqueta del fabricante.
            </div>
            @include('equipment.partials.form', ['room' => $room])
        </div>
    </div>
</div>
@endsection
