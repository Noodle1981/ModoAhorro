@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="bi bi-airplane-engines text-primary display-1"></i>
                    </div>
                    <h2 class="card-title mb-3">Modo Vacaciones</h2>
                    <p class="card-text text-muted mb-4">
                        El Asistente de Salida te ayudará a preparar tu casa para ahorrar energía y mantenerla segura mientras no estás.
                    </p>

                    <form action="{{ route('vacation.calculate', $entity->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="days" class="form-label h5">¿Por cuántos días te vas?</label>
                            <input type="number" class="form-control form-control-lg text-center" id="days" name="days" placeholder="Ej: 15" required min="1" autofocus>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-magic"></i> Generar Plan de Ahorro
                            </button>
                            <a href="{{ route('entities.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
