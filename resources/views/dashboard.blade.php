@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="bi bi-speedometer2"></i> Dashboard</h4>
                </div>
                <div class="card-body text-center">
                    <h5>¡Bienvenido, {{ auth()->user()->name }}!</h5>
                    @if($needsEntity)
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-house-door"></i> No tienes una entidad hogar creada.<br>
                            Por favor, agrega los datos de tu hogar para comenzar a usar ModoAhorro.
                        </div>
                        <a href="/entities/create" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Crear entidad hogar
                        </a>
                    @else
                        <div class="alert alert-success mt-3">
                            <i class="bi bi-house-door"></i> Tienes una entidad hogar registrada.<br>
                            <strong>{{ $entity->name }}</strong>
                        </div>
                        <a href="/entities" class="btn btn-success mt-2">
                            <i class="bi bi-list"></i> Ver entidad hogar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
