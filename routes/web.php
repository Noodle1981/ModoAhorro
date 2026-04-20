<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Autenticación
Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');
Route::post('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {
    // Entidades (Selector)
    Route::get('/entidades', 'App\Http\Controllers\DashboardController@index')->name('dashboard');
    Route::redirect('/dashboard', '/entidades');

    // Inicio / Resumen de Entidad (Panel con Sidebar)
    Route::get('/inicio', 'App\Http\Controllers\DashboardController@home')->name('home');

    // Activar una entidad específica
    Route::get('/entidades/{entity}/activate', function ($entityId) {
        session(['active_entity_id' => $entityId]);
        return redirect()->route('home');
    })->name('entities.activate');

    // Grupos Funcionales
    Route::prefix('gestion')->name('gestion.')->group(function () {
        // Contratos
        Route::get('/contratos', 'App\Http\Controllers\ContractController@index')->name('contracts');
        Route::post('/contratos', 'App\Http\Controllers\ContractController@store')->name('contracts.store');
        Route::put('/contratos/{contract}', 'App\Http\Controllers\ContractController@update')->name('contracts.update');
        Route::delete('/contratos/{contract}', 'App\Http\Controllers\ContractController@destroy')->name('contracts.destroy');
        Route::patch('/contratos/{contract}/toggle', 'App\Http\Controllers\ContractController@toggleActive')->name('contracts.toggle');
        
        // Módulo Térmico
        Route::prefix('thermal')->name('thermal.')->group(function () {
            Route::get('/wizard/{entity?}', 'App\Http\Controllers\Recommendations\ThermalComfortController@wizard')->name('wizard');
            Route::get('/result/{entity?}', 'App\Http\Controllers\Recommendations\ThermalComfortController@result')->name('result');
            Route::post('/wizard/{entity?}', 'App\Http\Controllers\Recommendations\ThermalComfortController@store')->name('store');
        });

        Route::get('/facturas', 'App\Http\Controllers\InvoiceController@index')->name('invoices');
        Route::post('/facturas', 'App\Http\Controllers\InvoiceController@store')->name('invoices.store');
        Route::put('/facturas/{invoice}', 'App\Http\Controllers\InvoiceController@update')->name('invoices.update');
        Route::delete('/facturas/{invoice}', 'App\Http\Controllers\InvoiceController@destroy')->name('invoices.destroy');

        Route::get('/infraestructura', 'App\Http\Controllers\InfrastructureController@index')->name('infrastructure');
        
        // Rooms
        Route::post('/ambientes', 'App\Http\Controllers\InfrastructureController@storeRoom')->name('rooms.store');
        Route::put('/ambientes/{room}', 'App\Http\Controllers\InfrastructureController@updateRoom')->name('rooms.update');
        Route::delete('/ambientes/{room}', 'App\Http\Controllers\InfrastructureController@destroyRoom')->name('rooms.destroy');
        
        // Equipment
        Route::post('/equipos', 'App\Http\Controllers\InfrastructureController@storeEquipment')->name('equipment.store');
        Route::put('/equipos/{equipment}', 'App\Http\Controllers\InfrastructureController@updateEquipment')->name('equipment.update');
        Route::delete('/equipos/{equipment}', 'App\Http\Controllers\InfrastructureController@destroyEquipment')->name('equipment.destroy');
    });

    Route::prefix('analisis')->name('analisis.')->group(function () {
        Route::get('/consumo-real', fn() => Inertia::render('Placeholder', ['title' => 'Análisis de Consumo Real']))->name('consumption');
        Route::get('/ajuste-uso', fn() => Inertia::render('Placeholder', ['title' => 'Ajuste de Uso']))->name('usage');
        Route::get('/optimizacion-horarios', fn() => Inertia::render('Placeholder', ['title' => 'Optimización de Horarios']))->name('grid-optimization');
    });

    Route::prefix('recomendaciones')->name('recomendaciones.')->group(function () {
        Route::get('/paneles-solares', fn() => Inertia::render('Placeholder', ['title' => 'Proyecto Paneles Solares']))->name('solar-panels');
        Route::get('/calefones-solares', fn() => Inertia::render('Placeholder', ['title' => 'Calefones Solares']))->name('solar-water-heater');
        Route::get('/reemplazos', fn() => Inertia::render('Placeholder', ['title' => 'Plan de Reemplazos Eficientes']))->name('replacements');
        Route::get('/consumo-fantasma', fn() => Inertia::render('Placeholder', ['title' => 'Análisis Consumo Fantasma']))->name('standby-analysis');
        Route::get('/mantenimiento', fn() => Inertia::render('Placeholder', ['title' => 'Plan de Mantenimiento']))->name('maintenance');
        Route::get('/vacaciones', fn() => Inertia::render('Placeholder', ['title' => 'Modo Vacaciones']))->name('vacation');
        Route::get('/medidor-inteligente', fn() => Inertia::render('Placeholder', ['title' => 'Medidor Inteligente (Demo)']))->name('smart-meter');
    });

    Route::prefix('sistema')->name('sistema.')->group(function () {
        Route::get('/administracion', fn() => Inertia::render('Placeholder', ['title' => 'Administración del Sistema']))->name('admin');
        Route::get('/benchmarks', fn() => Inertia::render('Placeholder', ['title' => 'Benchmarks de Eficiencia']))->name('benchmarks');
    });
});
