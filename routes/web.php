<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth', \App\Http\Middleware\CheckPlanEntities::class])->group(function () {
    // Rutas CRUD para contratos
    Route::get('/contracts', [\App\Http\Controllers\ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [\App\Http\Controllers\ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [\App\Http\Controllers\ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}/edit', [\App\Http\Controllers\ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [\App\Http\Controllers\ContractController::class, 'update'])->name('contracts.update');
    Route::delete('/contracts/{contract}', [\App\Http\Controllers\ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas CRUD para entidades
    Route::get('/entities', [\App\Http\Controllers\EntityController::class, 'index'])->name('entities.index');
    Route::get('/entities/create', [\App\Http\Controllers\EntityController::class, 'create'])->name('entities.create');
    Route::post('/entities', [\App\Http\Controllers\EntityController::class, 'store'])->name('entities.store');
    Route::get('/entities/{entity}', [\App\Http\Controllers\EntityController::class, 'show'])->name('entities.show');
    Route::get('/entities/{entity}/edit', [\App\Http\Controllers\EntityController::class, 'edit'])->name('entities.edit');
    Route::put('/entities/{entity}', [\App\Http\Controllers\EntityController::class, 'update'])->name('entities.update');
    Route::delete('/entities/{entity}', [\App\Http\Controllers\EntityController::class, 'destroy'])->name('entities.destroy');

    // Rutas CRUD para habitaciones (rooms) anidadas bajo entidad
    Route::get('/entities/{entity}/rooms', [\App\Http\Controllers\RoomController::class, 'index'])->name('rooms.index');
    Route::get('/entities/{entity}/rooms/create', [\App\Http\Controllers\RoomController::class, 'create'])->name('rooms.create');
    Route::post('/entities/{entity}/rooms', [\App\Http\Controllers\RoomController::class, 'store'])->name('rooms.store');
    Route::get('/entities/{entity}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'show'])->name('rooms.show');
    Route::get('/entities/{entity}/rooms/{room}/edit', [\App\Http\Controllers\RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/entities/{entity}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/entities/{entity}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('rooms.destroy');
    // Ruta para mostrar el contrato del medidor bajo entidad
    Route::get('/entities/{entity}/meter', [\App\Http\Controllers\ContractController::class, 'showForEntity'])->name('entities.meter.index');
        // Rutas CRUD para facturas (invoices) bajo entidad
        Route::get('/entities/{entity}/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('entities.invoices.index');
        Route::get('/entities/{entity}/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('entities.invoices.create');
        Route::post('/entities/{entity}/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('entities.invoices.store');
        Route::get('/entities/{entity}/invoices/{invoice}/edit', [\App\Http\Controllers\InvoiceController::class, 'edit'])->name('entities.invoices.edit');
        Route::put('/entities/{entity}/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('entities.invoices.update');
});
