@extends('layouts.app')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('entities.home.index') }}">Mis Hogares</a></li>
            <li class="breadcrumb-item"><a href="{{ route('entities.home.show', $entity->id) }}">{{ $entity->name }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <h2><i class="bi bi-pencil-square"></i> Editar hogar</h2>
    <p class="text-muted">Modifica los datos de tu hogar.</p>

    <div class="card shadow mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('entities.home.update', $entity->id) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre del hogar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $entity->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="locality_id" class="form-label">Localidad <span class="text-danger">*</span></label>
                            <select class="form-select @error('locality_id') is-invalid @enderror" 
                                    id="locality_id" name="locality_id" required>
                                <option value="">Selecciona una localidad</option>
                                @foreach($localities as $locality)
                                    <option value="{{ $locality->id }}" 
                                        {{ old('locality_id', $entity->locality_id) == $locality->id ? 'selected' : '' }}>
                                        {{ $locality->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('locality_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="address_street" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('address_street') is-invalid @enderror" 
                                   id="address_street" name="address_street" 
                                   value="{{ old('address_street', $entity->address_street) }}" required>
                            @error('address_street')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="address_postal_code" class="form-label">Código postal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('address_postal_code') is-invalid @enderror" 
                                   id="address_postal_code" name="address_postal_code" 
                                   value="{{ old('address_postal_code', $entity->address_postal_code) }}" required>
                            @error('address_postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="square_meters" class="form-label">Metros cuadrados <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('square_meters') is-invalid @enderror" 
                                       id="square_meters" name="square_meters" 
                                       value="{{ old('square_meters', $entity->square_meters) }}" min="1" required>
                                <span class="input-group-text">m²</span>
                                @error('square_meters')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="people_count" class="form-label">Cantidad de personas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('people_count') is-invalid @enderror" 
                                   id="people_count" name="people_count" 
                                   value="{{ old('people_count', $entity->people_count) }}" min="1" required>
                            @error('people_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Descripción (opcional)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="2">{{ old('description', $entity->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('entities.home.show', $entity->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
