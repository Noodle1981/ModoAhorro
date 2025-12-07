@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Nuevo Benchmark de Eficiencia</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('efficiency-benchmarks.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="equipment_type_id">Tipo de Equipo</label>
                    <select name="equipment_type_id" id="equipment_type_id" class="form-control" required>
                        <option value="">Seleccionar tipo...</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->category->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="meli_search_term">Término de Búsqueda Mercado Libre</label>
                    <input type="text" name="meli_search_term" id="meli_search_term" class="form-control" required placeholder="Ej: Aire Inverter Samsung">
                    <small class="form-text text-muted">Este texto se usará para buscar en la API de Mercado Libre.</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="efficiency_gain_factor">Factor de Ahorro (0.0 a 1.0)</label>
                        <input type="number" step="0.01" min="0" max="1" name="efficiency_gain_factor" id="efficiency_gain_factor" class="form-control" required placeholder="0.40">
                        <small class="form-text text-muted">Ej: 0.40 significa un ahorro del 40% respecto al equipo viejo.</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="average_market_price">Precio Promedio de Mercado ($)</label>
                        <input type="number" step="0.01" name="average_market_price" id="average_market_price" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="affiliate_link">Link de Afiliado (Opcional)</label>
                    <input type="url" name="affiliate_link" id="affiliate_link" class="form-control" placeholder="https://...">
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('efficiency-benchmarks.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection
