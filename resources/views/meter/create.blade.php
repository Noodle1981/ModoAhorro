@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Registrar Medidor</h1>
    <form method="POST" action="{{ route('entities.meter.store', $entity->id) }}">
        @csrf
        <div class="mb-3">
            <label for="serial_number" class="form-label">Número de Serie</label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" required>
        </div>
        <div class="mb-3">
            <label for="company_id" class="form-label">Empresa proveedora</label>
            <select class="form-control" id="company_id" name="company_id" required>
                <option value="">Seleccione empresa</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('entities.meter.index', $entity->id) }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
