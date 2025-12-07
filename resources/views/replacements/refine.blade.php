@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-magic mr-2"></i>Refinar Datos para Recomendación</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Ayúdanos a mejorar la recomendación para tu equipo <strong>{{ $equipment->name }}</strong>. 
                        Cuantos más datos nos des, más preciso será el cálculo de ahorro.
                    </p>

                    <form method="POST" action="{{ route('replacements.update_refinement', $equipment->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="acquisition_year" class="form-label">Año de Compra (Aprox)</label>
                                <input type="number" class="form-control" id="acquisition_year" name="acquisition_year" 
                                       min="1990" max="{{ date('Y') }}" 
                                       value="{{ old('acquisition_year', $equipment->acquisition_year) }}" 
                                       placeholder="Ej: 2015">
                                <small class="text-muted">Usamos esto para estimar el desgaste y la ineficiencia.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="energy_label" class="form-label">Etiqueta Energética</label>
                                <select class="form-select" id="energy_label" name="energy_label">
                                    <option value="">Desconozco</option>
                                    @foreach(['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E'] as $label)
                                        <option value="{{ $label }}" {{ old('energy_label', $equipment->energy_label) == $label ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Suele estar pegada en el frente del equipo.</small>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="capacity" class="form-label">Capacidad</label>
                                <input type="number" step="0.01" class="form-control" id="capacity" name="capacity" 
                                       value="{{ old('capacity', $equipment->capacity) }}" 
                                       placeholder="Ej: 3500">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="capacity_unit" class="form-label">Unidad</label>
                                <select class="form-select" id="capacity_unit" name="capacity_unit">
                                    <option value="">Seleccionar...</option>
                                    <option value="frigorias" {{ old('capacity_unit', $equipment->capacity_unit) == 'frigorias' ? 'selected' : '' }}>Frigorías (Aire)</option>
                                    <option value="litros" {{ old('capacity_unit', $equipment->capacity_unit) == 'litros' ? 'selected' : '' }}>Litros (Termotanque/Heladera)</option>
                                    <option value="kg" {{ old('capacity_unit', $equipment->capacity_unit) == 'kg' ? 'selected' : '' }}>Kg (Lavarropas)</option>
                                    <option value="btu" {{ old('capacity_unit', $equipment->capacity_unit) == 'btu' ? 'selected' : '' }}>BTU</option>
                                    <option value="watts" {{ old('capacity_unit', $equipment->capacity_unit) == 'watts' ? 'selected' : '' }}>Watts</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_inverter" name="is_inverter" value="1" {{ old('is_inverter', $equipment->is_inverter) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_inverter">
                                        ¿Es Tecnología Inverter?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar y Recalcular
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
