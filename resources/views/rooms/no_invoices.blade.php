@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Gestión de equipos</h1>
    <div class="alert alert-warning">
        Para gestionar equipos primero debes cargar al menos una factura para esta entidad.<br>
        <a href="{{ route('entities.invoices.create', $entity->id) }}" class="btn btn-primary mt-2">Cargar factura</a>
    </div>
    <a href="{{ route('rooms.index', $entity->id) }}" class="btn btn-secondary">Volver a habitaciones</a>
</div>
@endsection
