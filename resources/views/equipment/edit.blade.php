@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Equipo</h1>
    <a href="{{ route('rooms.equipment.dashboard', [$room->entity_id, $room->id]) }}" class="btn btn-secondary mb-3">Volver a equipos de la habitación</a>
    <form method="POST" action="{{ route('rooms.equipment.update', [$room->entity_id, $room->id, $equipment->id]) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nombre del Equipo</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $equipment->name }}" required>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">Categoría</label>
            <select class="form-control" id="category_id" name="category_id" required onchange="filtrarEquiposPorCategoria()">
                <option value="">Seleccione una categoría...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @if($equipment->category_id == $category->id) selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
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
                if (!option.value) continue;
                option.style.display = option.getAttribute('data-category') === categoriaId ? '' : 'none';
            }
        }
        window.onload = filtrarEquiposPorCategoria;
        </script>
        <div class="mb-3">
              <label for="nominal_power_w" class="form-label">Potencia Nominal (W)</label>
              <input type="number" class="form-control" id="nominal_power_w" name="nominal_power_w" value="{{ $equipment->nominal_power_w }}" required min="1" step="1" oninput="if(this.value<1)this.value=1;">
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de alta</label>
            <input type="text" class="form-control" value="{{ $equipment->created_at ? $equipment->created_at->format('d/m/Y') : '' }}" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
