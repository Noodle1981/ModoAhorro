@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-house-door"></i> Crear entidad hogar</h2>
    <div class="card shadow mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('entities.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del hogar</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="address_street" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="address_street" name="address_street" required>
                </div>
                <div class="mb-3">
                    <label for="address_postal_code" class="form-label">Código postal</label>
                    <input type="text" class="form-control" id="address_postal_code" name="address_postal_code" required>
                </div>
                <div class="mb-3">
                    <label for="locality_id" class="form-label">Localidad</label>
                    @if($localities->isEmpty())
                        <div class="alert alert-danger">
                            No hay localidades disponibles. Por favor, contacta al administrador o ejecuta los seeders.
                        </div>
                    @else
                        <select class="form-select" id="locality_id" name="locality_id" required>
                            <option value="">Selecciona una localidad</option>
                            @foreach($localities as $locality)
                                <option value="{{ $locality->id }}">
                                    {{ $locality->name }} ({{ $locality->province->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label for="square_meters" class="form-label">Metros cuadrados</label>
                    <input type="number" class="form-control" id="square_meters" name="square_meters" min="1" required>
                </div>
                <div class="mb-3">
                    <label for="people_count" class="form-label">Cantidad de personas</label>
                    <input type="number" class="form-control" id="people_count" name="people_count" min="1" required>
                </div>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar entidad</button>
            </form>
        </div>
    </div>
</div>
@endsection
