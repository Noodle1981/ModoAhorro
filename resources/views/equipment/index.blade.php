@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Equipos del Hogar</h1>
    <a href="{{ route('equipment.create') }}" class="btn btn-success mb-3">Agregar Equipo</a>
    <a href="{{ route('equipment.create_portable') }}" class="btn btn-info mb-3 ms-2">Cargar Equipo Portátil</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipments as $equipment)
                <tr>
                    <td>{{ $equipment->id }}</td>
                    <td>{{ $equipment->name }}</td>
                    <td>{{ $equipment->category->name ?? '-' }}</td>
                    <td>{{ $equipment->type->name ?? '-' }}</td>
                    <td>
                        @if($equipment->is_active)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">De baja</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('equipment.edit', $equipment->id) }}" class="btn btn-primary btn-sm">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
