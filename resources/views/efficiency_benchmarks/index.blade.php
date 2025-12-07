@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Configuración de Reemplazos (Benchmarks)</h1>
        <a href="{{ route('efficiency-benchmarks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nuevo Benchmark
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tipo de Equipo</th>
                            <th>Término ML</th>
                            <th>Factor Ahorro</th>
                            <th>Precio Promedio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($benchmarks as $benchmark)
                            <tr>
                                <td>
                                    {{ $benchmark->equipmentType->name }}
                                    <small class="d-block text-muted">{{ $benchmark->equipmentType->category->name }}</small>
                                </td>
                                <td>{{ $benchmark->meli_search_term }}</td>
                                <td>{{ number_format($benchmark->efficiency_gain_factor * 100, 0) }}%</td>
                                <td>${{ number_format($benchmark->average_market_price, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('efficiency-benchmarks.edit', $benchmark) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('efficiency-benchmarks.destroy', $benchmark) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro quieres eliminar este benchmark?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
