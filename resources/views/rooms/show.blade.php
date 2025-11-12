@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-door-open"></i> Detalles de la habitación</h2>
    <div class="card shadow mt-3">
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Nombre:</strong> {{ $room->name }}</li>
                <li class="list-group-item"><strong>Descripción:</strong> {{ $room->description }}</li>
            </ul>
            <div class="mt-3">
                <a href="{{ route('rooms.edit', [$entity->id, $room->id]) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
                <a href="{{ route('rooms.index', $entity->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
