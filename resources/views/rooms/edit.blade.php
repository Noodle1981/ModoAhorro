@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-pencil-square"></i> Editar habitación</h2>
    <div class="card shadow mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('rooms.update', [$entity->id, $room->id]) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre de la habitación</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $room->name }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="2">{{ $room->description }}</textarea>
                </div>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Actualizar habitación</button>
            </form>
        </div>
    </div>
</div>
@endsection
