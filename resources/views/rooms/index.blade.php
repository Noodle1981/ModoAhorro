@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-door-open"></i> Habitaciones de la entidad</h2>
        <a href="{{ route('rooms.create', $entity->id) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva habitación
        </a>
    </div>
    <div class="card shadow">
        <div class="card-body">
            @if($rooms->isEmpty())
                <p class="text-muted">No hay habitaciones registradas aún.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                                <tr>
                                    <td>{{ $room->name }}</td>
                                    <td>{{ $room->description }}</td>
                                    <td>
                                        <a href="{{ route('rooms.show', [$entity->id, $room->id]) }}" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('rooms.edit', [$entity->id, $room->id]) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                        <form action="{{ route('rooms.destroy', [$entity->id, $room->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta habitación?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="mt-4">
                <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a la entidad
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
