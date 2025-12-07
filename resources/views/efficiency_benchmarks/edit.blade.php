@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Editar Benchmark</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('efficiency-benchmarks.update', $efficiencyBenchmark) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="equipment_type_id">Tipo de Equipo</label>
                    <select name="equipment_type_id" id="equipment_type_id" class="form-control" required>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ $efficiencyBenchmark->equipment_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->category->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="meli_search_term">Término de Búsqueda Mercado Libre</label>
                    <input type="text" name="meli_search_term" id="meli_search_term" class="form-control" value="{{ $efficiencyBenchmark->meli_search_term }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="efficiency_gain_factor">Factor de Ahorro (0.0 a 1.0)</label>
                        <input type="number" step="0.01" min="0" max="1" name="efficiency_gain_factor" id="efficiency_gain_factor" class="form-control" value="{{ $efficiencyBenchmark->efficiency_gain_factor }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="average_market_price">Precio Promedio de Mercado ($)</label>
                        <input type="number" step="0.01" name="average_market_price" id="average_market_price" class="form-control" value="{{ $efficiencyBenchmark->average_market_price }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="affiliate_link">Link de Afiliado (Opcional)</label>
                    <input type="url" name="affiliate_link" id="affiliate_link" class="form-control" value="{{ $efficiencyBenchmark->affiliate_link }}">
                </div>

                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('efficiency-benchmarks.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection
