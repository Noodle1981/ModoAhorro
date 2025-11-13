<form method="POST" action="{{ route('rooms.equipment.store', [$room->entity_id, $room->id]) }}">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Nombre del Equipo</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="category_id" class="form-label">Categoría</label>
        <select class="form-control" id="category_id" name="category_id" required onchange="filtrarEquiposPorCategoria()">
            <option value="">Seleccione una categoría...</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label for="type_id" class="form-label">Equipo</label>
        <select class="form-control" id="type_id" name="type_id" required>
            <option value="">Seleccione un equipo...</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" data-category="{{ $type->category_id }}">{{ $type->name }}</option>
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
            option.style.display = !categoriaId || option.getAttribute('data-category') === categoriaId ? '' : 'none';
        }
        equipoSelect.value = '';
    }
    </script>
    <div class="mb-3">
        <label for="nominal_power_w" class="form-label">Potencia Nominal (W)</label>
        <input type="number" class="form-control" id="nominal_power_w" name="nominal_power_w" required min="1" step="1" oninput="if(this.value<1)this.value=1;">
    </div>
    <div class="mb-3">
        <label for="cantidad" class="form-label">Cantidad</label>
        <input type="number" class="form-control" id="cantidad" name="cantidad" value="1" min="1" step="1" required>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
