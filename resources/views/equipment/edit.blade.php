@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Equipo</h1>
    <a href="{{ route('rooms.equipment.dashboard', [$room->entity_id, $room->id]) }}" class="btn btn-secondary mb-3">Volver a equipos de la habitación</a>
    <form method="POST" action="{{ route('rooms.equipment.update', [$room->entity_id, $room->id, $equipment->id]) }}">
        @csrf
        </div>
        <div class="mb-3">
            <label for="type_id" class="form-label">Equipo</label>
            <select class="form-control" id="type_id" name="type_id" required>
                <option value="">Seleccione un equipo...</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" data-category="{{ $type->category_id }}" @if($equipment->type_id == $type->id) selected @endif>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <script>
        function filtrarEquiposPorCategoria() {
            var categoriaId = document.getElementById('category_id').value;
            var equipoSelect = document.getElementById('type_id');
            for (var i = 0; i < equipoSelect.options.length; i++) {
                var option = equipoSelect.options[i];
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
